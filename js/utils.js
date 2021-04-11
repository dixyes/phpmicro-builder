
const os = require("os");

const argmap = {
  "bz2": "--with-bz2",
  "openssl": "--with-openssl",
  "zip": "--enable-zip",
  "ffi": "--with-ffi",
  "zlib": "--enable-zlib",
  "curl": "--with-curl",
  "redis": "--enable-redis",
  "mbstring": "--enable-mbstring",
  "mysqli": "--with-mysqli",
  "pdo-mysql": "--with-pdo-mysql",
  "ctype": "--enable-ctype",
  "pdo": "--enable-pdo",
  "fileinfo": "--enable-fileinfo",
  "filter": "--enable-filter",
  "mbregex": "--enable-mbregex",
  "phar": "--enable-phar",
  "pcntl": "--enable-pcntl",
  "posix": "--enable-posix",
  "shmop": "--enable-shmop",
  "session": "--enable-session",
  "tokenizer": "--enable-tokenizer",
  "sockets": "--enable-sockets",
  "sysvmsg": "--enable-sysvmsg",
  "sysvsem": "--enable-sysvsem",
  "sysvshm": "--enable-sysvshm",
};

function extarg(name){
  if("" == name){
    return "";
  }
  if(!argmap[name]){
    throw `unknown extension name ${name}`;
  }
  return argmap[name];
}

let defexts = [
  "ctype",
  "pdo",
  "pdo-mysql",
  "fileinfo",
  "filter",
  "tokenizer",
  "session",
  "mbregex",
  "sockets",
];
switch(os.type()){
  case "Windows_NT":
    break;
  case "Darwin":
    defexts = defexts.concat([
      "pcntl",
      "posix",
      "shmop",
      "sysvshm",
    ]);
    break;
  default:
    throw "not implemented";
}

module.exports = {extarg:extarg, defexts:defexts};
