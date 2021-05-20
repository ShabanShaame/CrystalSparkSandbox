"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.NotaryManager = void 0;
var argv = require('minimist')(process.argv.slice(2), {
    string: ['from', 'contract', 'to', 'key'],
    int: ['quantity'] // --lang xml
});
var NotaryManager = /** @class */ (function () {
    function NotaryManager() {
        this.status = CSNstatus.ok;
        this.verbose = false;
        this.arguments = null;
        this.response = new CSNResponse(CSNstatus.ok, '');
    }
    NotaryManager.prototype.returnError = function (response) {
        this.status = CSNstatus.error;
        this.returnResponse(response, false);
    };
    NotaryManager.prototype.flatTokenPath = function () {
        this.arguments.tokenPath = JSON.parse(this.arguments.tokenPath);
    };
    NotaryManager.prototype.returnResponse = function (response, rawLastLine) {
        if (rawLastLine === void 0) { rawLastLine = true; }
        this.response.setMessage(response);
        this.response.status = this.status;
        console.log(JSON.stringify(this.response));
        if (rawLastLine)
            console.log(rawLastLine);
        process.exit();
    };
    NotaryManager.prototype.validateInputs = function (required) {
        var error = [];
        var receivedArgs = this.arguments;
        required.forEach(function (argument) {
            if (!receivedArgs[argument])
                error.push(argument);
        });
        if (error.length > 0) {
            var fullError = { message: "missing arguments", list: error };
            this.returnError(fullError);
        }
    };
    NotaryManager.prototype.parseIntent = function () {
        var _this = this;
        var argv = require('minimist')(process.argv.slice(2), {
            string: ["command", "chain", 'from', 'contract', 'to', 'key', 'query', 'legacy', 'feePayerPrivate', 'feePayerPublic', 'tx']
        });
        this.arguments = argv;
        if (this.arguments.tokenPath)
            this.flatTokenPath();
        if (argv.chain == 'bitcoin')
            argv.chain = 'counterparty'; // hack to push counterpaty
        if (argv.chain == 'goerli') {
            argv.chain = 'ethereum';
            this.arguments.network = 'goerli';
        }
        if (argv.chain == 'bitcoin')
            argv.chain = 'counterparty'; // hack to push counterpaty
        Promise.resolve().then(function () { return require("./" + argv.chain + "/ChainNotary"); }).then(function (notary) {
            var my = new notary.ChainNotary(_this);
            _this.response.meta.chain = _this.arguments.network;
            if (_this.arguments.legacy == 'false') {
                my.legacyResponseType = false;
            }
            try {
                switch (_this.arguments.command) {
                    case 'send':
                        my.send();
                        break;
                    case 'balanceOf':
                        my.balanceOf();
                        break;
                    case 'ownerOf':
                        my.ownerOf();
                        break;
                    case 'createWallet':
                        my.createWallet();
                        break;
                    case 'parseContractEvents':
                        my.chainParseTransactions();
                        break;
                    case 'mintTo':
                        my.mintTo();
                        break;
                    case 'getPending':
                        my.getPendingTransactions();
                        break;
                    case 'createSend':
                        my.createSend();
                        break;
                    case 'broadcast':
                        my.broadcast();
                        break;
                    case 'getContractData':
                        my.getContractData();
                        break;
                    default:
                        _this.returnError({ message: "Command not found " + _this.arguments.command + " " });
                }
            }
            catch (e) {
                console.log("EEERRROOROOROR");
                _this.returnError({ message: "error" + e + " " });
            }
        })
            .catch(function (err) {
            console.log(err);
            _this.log("./" + argv.chain + "/ChainNotary");
            _this.returnError({ message: "CSNotary unsupported " + argv.chain + " chain" });
        });
        this.log(argv.command);
        this.log(argv.chain);
    };
    NotaryManager.prototype.log = function (log) {
        if (this.verbose) {
            console.log(log);
        }
    };
    return NotaryManager;
}());
exports.NotaryManager = NotaryManager;
var CSNResponse = /** @class */ (function () {
    function CSNResponse(status, message) {
        this.meta = { version: 0.1, chain: 'unknown' };
        this.status = CSNstatus.ok;
        this.data = null;
        this.data = message;
        this.status = status;
    }
    CSNResponse.prototype.return = function () {
        var i = 3;
    };
    CSNResponse.prototype.setChain = function (chain) {
        this.meta.chain = chain;
    };
    CSNResponse.prototype.setMessage = function (message) {
        this.data = message;
    };
    return CSNResponse;
}());
var CSNstatus;
(function (CSNstatus) {
    CSNstatus["ok"] = "ok";
    CSNstatus["error"] = "error";
})(CSNstatus || (CSNstatus = {}));
//# sourceMappingURL=NotaryManager.js.map