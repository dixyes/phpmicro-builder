
'use strict';

const { core, github, exec } = require(__dirname + "/ghwrap.dist/index.js");
const fsp = require('fs').promises;
const fs = require('fs');
const http = require('http');
const https = require('https');

const srcinfos = module.exports.srcinfos = {exts:{}, deps:{}};
const octokit = github.getOctokit(core.getInput('githubtoken'), {required: true});

function dl(url, dest){
  return new Promise((resolve,reject)=>{
    let get = (url, cb) => {
      let urlobj = new URL(url);
      let proto;
      if(urlobj.protocol == "https:"){
        proto = https;
      }else if(urlobj.protocol == "http:"){
        proto = http;
      }else{
        reject(`not support url proto in "${url}"`);
      }
      proto.get(urlobj, (resp)=>{
        let newurl = resp.headers.location;
        if(newurl){
          if(newurl.match(/^http/)){
            urlobj = new URL(newurl);
            return get(urlobj, cb);
          }else{
            urlobj.pathname = newurl;
            return get(urlobj, cb);
          }
        }
        return cb(resp);
      }).on("error", reject);
    };
    let file = fs.createWriteStream(dest);
    get(url, resp => {
      resp.pipe(file);
      file.on('finish', () =>{
        file.close((e) => {if(e) { reject(e); } else { resolve(); }});
      });
    });
  });
}

async function prepareghrel(n, srcinfo){
  const arr = srcinfo.repo.split("/");
  const options = {
    owner: arr[0],
    repo: arr[1],
  };
  
  const {data: {assets: assets}} = await octokit.repos.getLatestRelease(options);
  //console.log(assets);
  const fnre = new RegExp(srcinfo.match);
  const { browser_download_url: url, name: fn } = assets.filter((e)=>{
    //console.log("check", e.name);
    if(e.name.match(fnre)){
      return true;
    }
    return false;
  })[0];

  //console.log(url, fn);
  try{
    await dl(url, fn);
  }catch(e){
    return {success: false, msg: e};
  }
  
  return {success: true, path: fn};
}
async function preparegit(n, srcinfo){
  let path = `${n}-${srcinfo.ref}`;
  try{
    await exec.exec("git", ["clone", "--single-branch", "-b", srcinfo.ref, srcinfo.repo, path])
  }catch(e){
    return {success: true, msg: e};
  }
  return {success: true, path: path};
}
async function prepareurl(n, srcinfo){
  let fn;
  if(srcinfo.name){
    fn = srcinfo.name;
  }else{
    fn = `${n}.tar`
  }
  try{
    await dl(srcinfo.url, fn);
  }catch(e){
    return {success: false, msg: e};
  }
  return {success: true, path: fn};
}

async function mergeinfo(){
  const infoFile = await fsp.open(__dirname + "/src.json", "r");
  let info = JSON.parse(await infoFile.readFile("utf-8"));
  //console.log(info);
  
  ["deps", "exts"].map((e)=>{
    Object.assign(srcinfos[e], info[e]);
  });

  const infoStr = core.getInput('srcinfos').trim();
  if("" == infoStr){
    return ;
  }
  info = JSON.parse(infoStr);
  //console.log(info);

  ["deps", "exts"].map((e)=>{
    Object.assign(srcinfos[e], info[e]);
  });
  //console.log(srcinfos);
}

async function prepare(){
  await mergeinfo();

  let ret = {
    success: true,
    deps: {},
    exts: {},
  }
  
  const tasks = {
    deps: core.getInput('deps').split(",").map(e=>e.trim()).filter(e=>e!=""),
    exts: core.getInput('exts').split(",").map(e=>e.trim()).filter(e=>e!=""),
  };

  let process = (scope, k)=>{
    if(!srcinfos[scope] || !srcinfos[scope][k]){
      ret[scope][k] = {success: false ,msg: `No info for ${scope} ${k}, try add it in inputs.srcinfos`};
      return;
    }
    if(ret[scope][k]){
      return;
    }
    let srcinfo = srcinfos[scope][k];
    if(srcinfo.requires){
      srcinfo.requires.map((req)=>{
        process("deps", req);
      });
    }
    switch(srcinfo.type){
      case "ghrel":
        ret[scope][k] = prepareghrel(k, srcinfo);
        break;
      case "git":
        ret[scope][k] = preparegit(k, srcinfo);
        break;
      case "url":
        ret[scope][k] = prepareurl(k, srcinfo);
        break;
      case "none":
        ret[scope][k] = {success: true};
        break;
      default:
        ret[scope][k] = {success: false ,msg: `Unknown type ${srcinfo.type} for ${scope} ${k}`};
    }
  }

  ["deps", "exts"].map((scope)=>{
    tasks[scope].map((k)=>{
      process(scope, k);
    });
  });

  for(let si in ["deps", "exts"]){
    let scope = ["deps", "exts"][si];
    for(let k in ret[scope]){
      try{
        ret[scope][k] = await ret[scope][k];
        if(!ret[scope][k].success){
          ret.success = false;
        }
      }catch(e){
        ret.success = false;
        ret[scope][k] = {success: false, msg: e};
      }
    }
  }

  //console.log("end",ret);
  return ret
}

module.exports.prepare=prepare;
