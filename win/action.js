// action builder for windows
'use strict';

const { prepare } = require(__dirname + "/../js/prepare.js");
const { io, core, exec } = require(__dirname + "/../js/ghwrap.dist/index.js");
const fs = require("fs");
const fsp = require("fs").promises;

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

  // prepare source codes
  console.log("::group::Prepare dependencies and extensions source");
  let ret = await prepare();
  if (!ret.success){
    core.setFailed(`Cannot fetch all deps and exts: ${ret}`);
  }
  console.log("::endgroup::");

  // prepare deps
  console.log("::group::Prepare dependencies");
  // we build these dependencies sync, they may rely on each other
  let ordereddeps = [];
  for(let k in ret.deps){
    let dep = prepare.srcinfos.deps[k];
    if(dep && dep.requires){
      for(let r in dep.requires){
        if (! ordereddeps.includes(r)){
          ordereddeps.push(dep);
        }
      }
    }
    ordereddeps.push(k);
  };
  ordereddeps.reduce((a,c)=>{
    console.log(`making dep ${k}`);
  }, null);
  console.log("::endgroup::");

  throw "not implemented"

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
