// action builder for windows
'use strict';

const { prepare, srcinfos } = require(__dirname + "/../js/prepare.js");
const { io, core, exec } = require(__dirname + "/../js/ghwrap.dist/index.js");
const fs = require("fs");
const fsp = require("fs").promises;

const tools_path = "C:\\tools\\phpdev";

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

  // prepare php-sdk-binary-tools
  console.log("::group::Prepare php-sdk-binary-tools");
  await exec.exec("git", ["clone", "--single-branch", "-b", "master", "https://github.com/Microsoft/php-sdk-binary-tools", "${tools_path}\\php-sdk-binary-tools"]);
  console.log("::endgroup::");

  // prepare deps
  console.log("::group::Prepare dependencies");
  console.log("::endgroup::");
  // we build these dependencies sync, they may rely on each other
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

  for(let c in ordereddeps){
    console.log(`::group::Making dep ${c}`);
    // TODO: maybe we can support vc{9,11,14,15}?
    process.chdir(c);
    await exec.exec("cmd",[
      "/C",
      "${tools_path}\\php-sdk-binary-tools\\phpsdk-vs16-x64.bat",
      "C:\\Program Files\\PowerShell\\7\\pwsh.exe",
      `${c}.sh`],{
        env:{
          "INSTPATH":"..\\deps",
          "BUILDPATH":".",
          "SRCPATH":".",
        }
      });
    process.chdir("..");
    console.log("::endgroup::");
  }

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
