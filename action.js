// dispatcher for different systems

const {core} = require("./js/ghwrap.dist/index.js");

async function mian(){
  const os = require("os");
  let builder, ret;
  switch(os.type()){
    case "Linux":
      builder = await require("./linux/action.js");
      return builder.start();
    case "Windows_NT":
      builder = await require("./win/action.js");
      return builder.start();
    case "Darwin":
    default:
      throw "not implemented";
  }
}

mian().catch(core.setFailed);

process.on('unhandledRejection', (reason, p) => {
  console.log('Unhandled Rejection at: Promise', p, 'reason:', reason);
  // application specific logging, throwing an error, or other logic here
  core.setFailed(reason);
  process.exit(1);
});