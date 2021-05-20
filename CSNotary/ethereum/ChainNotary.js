"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ChainNotary = void 0;
var ChainNotary_1 = require("../evm/ChainNotary");
var web3 = require('web3');
require('dotenv').config();
var Tx = require('ethereumjs-tx').Transaction;
var ChainNotary = /** @class */ (function (_super) {
    __extends(ChainNotary, _super);
    function ChainNotary() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    ChainNotary.prototype.getWeb3Provider = function () {
        var url = '';
        switch (this.manager.arguments.network) {
            case "rinkeby":
                url = 'https://rinkeby.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                break;
            case "ropsten":
                url = 'https://ropsten.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                break;
            case "goerli":
                url = 'https://goerli.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                this.chainId = 5;
                break;
            default:
                url = 'https://mainnet.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                break;
        }
        var web3Instance = new web3(new web3.providers.HttpProvider(url));
        return web3Instance;
    };
    ChainNotary.prototype.getWeb3Subprovider = function () {
        var web3 = this.getWeb3Provider();
        return web3.eth;
    };
    ChainNotary.prototype.getPendingTransactions = function () {
        return __awaiter(this, void 0, void 0, function () {
            var subProvider, options, web3;
            return __generator(this, function (_a) {
                subProvider = this.getWeb3Subprovider();
                // @ts-ignore
                subProvider.getTransactionReceipt('0xd2f38cf875e92a5bc836093e7770ff3ee059505d37e13449261c8c98edc7c1b0').then(console.log);
                return [2 /*return*/];
            });
        });
    };
    ChainNotary.prototype.send = function () {
        return __awaiter(this, void 0, void 0, function () {
            var web3Instance, validation, CONTRACT_ADDRESS, RECEIVER_ADDRESS, privateKey, account, sender, gasPriceGwei, contract, count, gasLimit, rawTransaction, tx, privKey, response, serializedTx, _a, err_1, e_1;
            return __generator(this, function (_b) {
                switch (_b.label) {
                    case 0:
                        _b.trys.push([0, 8, , 9]);
                        web3Instance = this.getWeb3Provider();
                        return [4 /*yield*/, this.manager.validateInputs(['from', 'contract', 'to', 'key'])];
                    case 1:
                        validation = _b.sent();
                        CONTRACT_ADDRESS = this.manager.arguments.contract;
                        RECEIVER_ADDRESS = this.manager.arguments.to;
                        privateKey = this.manager.arguments.key;
                        account = web3Instance.eth.accounts.privateKeyToAccount(ChainNotary_1.ChainNotaryEvm.addOx(privateKey));
                        sender = web3Instance.eth.accounts.wallet.add(account);
                        web3Instance.eth.defaultAccount = account.address;
                        return [4 /*yield*/, web3Instance.eth.getGasPrice()];
                    case 2:
                        gasPriceGwei = _b.sent();
                        contract = this.contractStandardRouting(CONTRACT_ADDRESS, web3Instance.eth);
                        return [4 /*yield*/, web3Instance.eth.getTransactionCount(sender.address, 'pending')];
                    case 3:
                        count = _b.sent();
                        gasLimit = 200000;
                        rawTransaction = {
                            "from": sender.address,
                            "gasPrice": web3.utils.toHex(gasPriceGwei),
                            //"gasPrice": web3.utils.toHex(gasPriceGwei * 1e9),
                            "gasLimit": web3.utils.toHex(gasLimit),
                            "to": CONTRACT_ADDRESS,
                            "value": "0x0",
                            "data": this.sendMethodRouting(contract, RECEIVER_ADDRESS),
                            'nonce': web3.utils.toHex(count),
                            'chainId': web3.utils.toHex(this.chainId)
                        };
                        tx = new Tx(rawTransaction, { 'chain': this.manager.arguments.network });
                        privKey = Buffer.from(ChainNotary_1.ChainNotaryEvm.removeOx(this.manager.arguments.key), 'hex');
                        response = {};
                        tx.sign(privKey);
                        serializedTx = tx.serialize();
                        _b.label = 4;
                    case 4:
                        _b.trys.push([4, 6, , 7]);
                        _a = response;
                        return [4 /*yield*/, this.waitForHash('0x' + serializedTx.toString('hex'))];
                    case 5:
                        _a.txHash = _b.sent();
                        return [3 /*break*/, 7];
                    case 6:
                        err_1 = _b.sent();
                        response.error = err_1.message;
                        this.response = response;
                        this.manager.returnError(response);
                        return [2 /*return*/];
                    case 7:
                        this.response = response;
                        _super.prototype.send.call(this);
                        return [3 /*break*/, 9];
                    case 8:
                        e_1 = _b.sent();
                        console.log(e_1.message);
                        this.manager.returnError(e_1.message);
                        return [3 /*break*/, 9];
                    case 9: return [2 /*return*/];
                }
            });
        });
    };
    return ChainNotary;
}(ChainNotary_1.ChainNotaryEvm));
exports.ChainNotary = ChainNotary;
/*
 * web3Instance.eth.sendSignedTransaction('0x' + serializedTx.toString('hex'))
 .on('transactionHash', function (hash) {
                response.txHash = hash ;

        })
 .on('receipt', function (receipt) {

                response.receipt = receipt ;
            })
 .on('error', function (err) {


            }).then( (value) => {
            this.response = response;
            super.send();
        });
 */
//# sourceMappingURL=ChainNotary.js.map