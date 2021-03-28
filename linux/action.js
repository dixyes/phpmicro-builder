// action builder for linux
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
    await io.cp(actiondir + '/linux', workdir, { recursive: true, force: false });
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

  // generate docketignore
  console.log("::group::Generate .dockerignore");
  const difile = await fsp.open(".dockerignore", "w");
  let sources = ["php-src", "php-src/sapi/micro"];
  for(let k in ret.exts){
    sources.push(ret.exts[k].path);
  };
  for(let k in ret.deps){
    sources.push(ret.deps[k].path);
  };
  await difile.write(sources.map((e)=>{
    return `${e}/.git`;
  }).reduce((a,c)=>{
    return `${a}\n${c}`;
  }));
  difile.close();
  console.log("::endgroup::");

  // prepare arguments
  console.log("::group::Generate build args");
  let args = core.getInput("args").split(/\s+/).filter(e=>e!="");
  for(let k in ret.exts){
    if(ret.exts[k].path){
      args.push(`+ext,${k},srcfile=${ret.exts[k].path}`);
    }else{
      // extensions without src file
      args.push(`+ext,${k}`);
    }
  };
  for(let k in ret.deps){
    args.push(`+dep,${k},srcfile=${ret.deps[k].path}`);
  };
  console.log("::endgroup::");

  console.log("::group::Start build with args");
  await exec.exec("./linux/make.sh", args);
  console.log("::endgroup::");

  if("1" == core.getInput("runtests").trim()){
    console.log("::group::Start test");
    await exec.exec("sh", ["-c", "docker run -v `realpath linux/test.sh`:/work/test.sh -v `realpath linux/rmtests.txt`:/work/iwillrmthesetestsonthismachine -w /work/php dixyes/microbuilder /work/test.sh INACTION; exit 0"]);
    console.log("::endgroup::");
  }
}

module.exports.start = start;
