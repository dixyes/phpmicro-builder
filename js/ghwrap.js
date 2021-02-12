
// ncc build js\ghwrap.js -m -o js\ghwrap.dist
module.exports ={
    //"artifact": require("@actions/artifact"),
    //"cache": require("@actions/cache"),
    "exec": require("@actions/exec"),
    "core": require("@actions/core"),
    "github": require("@actions/github"),
    //"glob": require("@actions/glob"),
    "io": require("@actions/io"),
    //"tool-cache": require("@actions/tool-cache"),
};
