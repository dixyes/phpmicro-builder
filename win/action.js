// action builder for windows
'use strict';

const { prepare, srcinfos } = require(__dirname + "/../js/prepare.js");
const { io, core, exec } = require(__dirname + "/../js/ghwrap.dist/index.js");
const fs = require("fs");
const fsp = require("fs").promises;

const tools_path = "C:\\tools\\phpdev";

async function extractsrc(ret, name){
  let src;
  if(ret.deps[name]){
    src = ret.deps[name];
  }else if(ret.exts[name]){
    src = ret.exts[name];
  }else{
    throw `no such source code: ${name}`;
  }
  
  switch (src.type){
    case "ghrel":
    case "url":
      let tarname;
      if(src.path.endsWith("xz")){
        await exec.exec('"C:\\Program Files\\7-Zip\\7z.exe"', [ 'x', src.path ]);
        tarname = src.path.replace(/\.xz$/, "");
      }else{
        tarname = src.path;
      }

      await exec.exec("cmd", [ "/C", "MKDIR", name]);
      await exec.exec("tar", [ "-x", "--strip-components=1", "-C", name, "-f", tarname]);
      return name;
    case "git":
      return `${name}-${src.ref}`;
    // no "none" case: should be filtered before called
    default:
      throw "unknown source type";
  }
}

async function start(){
  // prepare build dir
  console.log("::group::Prepare build dir");
  let actiondir = fs.realpathSync(".");
  let workdir = core.getInput("workdir").trim();
  if(""!=workdir){
    await io.mkdirP(workdir);
  }
  if(fs.realpathSync(workdir) != actiondir){
    process.chdir(workdir);
    await io.cp(actiondir + '/files', workdir, { recursive: true, force: false });
    await io.cp(actiondir + '/patches', workdir, { recursive: true, force: false });
    await io.cp(actiondir + '/win', workdir, { recursive: true, force: false });
  }
  console.log("::endgroup::");

  // prepare source codes
  console.log("::group::Prepare dependencies and extensions source");
  let ret = await prepare();
  if (!ret.success){
    core.setFailed(`Cannot fetch all deps and exts: ${ret}`);
  }
  console.log("::endgroup::");
  

  // prepare php-sdk-binary-tools
  console.log("::group::Prepare php-sdk-binary-tools");
  await exec.exec("git", ["clone", "--single-branch", "-b", "master", "https://github.com/Microsoft/php-sdk-binary-tools", "php-sdk-binary-tools"]);
  console.log("::endgroup::");

  // prepare deps
  console.log("::group::Prepare dependencies");
  console.log("::endgroup::");
  // we build these dependencies sync, because one may rely on another
  let ordereddeps = [];
  for(let k in ret.deps){
    let dep = srcinfos.deps[k];
    if(dep && dep.requires){
      ordereddeps.concat(
        dep.requires.filter((r)=>{
          if (! ordereddeps.includes(r)){
            return true;
          }
        })
      );
    }
    ordereddeps.push(k);
  };

  for(let depi in ordereddeps){
    let dep = ordereddeps[depi];
    console.log(`::group::Making dep ${dep}`);
    process.chdir(await extractsrc(ret, dep));
    // TODO: maybe we can support vc{9,11,14,15}?
    await exec.exec('powershell', [ `..\\win\\depsbuild\\${dep}.ps1`, '-InstDir', '..\\deps' ]);
    process.chdir("..");
    console.log("::endgroup::");
  }

  throw "not implemented";

  // prepare php and micro sources
  console.log("::group::Prepare php and micro source");
  let phpref = core.getInput("phpref");
  await exec.exec("git", ["clone", "--single-branch", "-b", phpref, "https://github.com/php/php-src", "php-src"]);
  let microref = core.getInput("microref");
  await exec.exec("git", ["clone", "--single-branch", "-b", microref, "https://github.com/longyan/phpmicro", "php-src/sapi/micro"]);
  process.chdir("php-src");
  let patches = core.getInput("patches").split(",").map(e=>e.trim()).filter(e=>e!="");
  for(let pi in patches){
    await exec.exec("patch", ["-p1", "-i", `sapi/micro/patches/${patches[pi]}`]);
  };
  process.chdir("..");
  console.log("::endgroup::");

  console.log("::group::Start build with args");
  await exec.exec("powershell ..\\win\\make.ps1");
  console.log("::endgroup::");

  if("1" == core.getInput("runtests").trim()){
    console.log("::group::Start test");
    // TODO: tests
    console.log("::endgroup::");
  }
}

module.exports.start = start;
