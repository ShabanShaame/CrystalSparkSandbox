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
import { ChainNotaryBase } from "../ChainNotaryBase";
import { ERC20, ERC721, MINTABLE } from "./commonABI";
import { ChainParser } from "./ChainParser";
var web3 = require('web3');
require('dotenv').config();
var Tx = require('ethereumjs-tx');
var ChainNotaryEvm = /** @class */ (function (_super) {
    __extends(ChainNotaryEvm, _super);
    function ChainNotaryEvm() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.chainId = 1;
        _this.OxOnPrivateKey = false;
        return _this;
    }
    // @ts-ignore
    ChainNotaryEvm.prototype.getWeb3Provider = function () {
        this.manager.returnError({ "error": "implementation error" });
    };
    ChainNotaryEvm.prototype.getWeb3Subprovider = function () {
        var me = this.getWeb3Provider();
        return me.eth;
    };
    ChainNotaryEvm.prototype.send = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                _super.prototype.send.call(this);
                return [2 /*return*/];
            });
        });
    };
    ChainNotaryEvm.prototype.convertPrivateKey = function (privateKey) {
        if (this.OxOnPrivateKey)
            return ChainNotaryEvm.removeOx(privateKey);
        return ChainNotaryEvm.addOx(privateKey);
    };
    ChainNotaryEvm.removeOx = function (privateKey) {
        if (privateKey.startsWith('0x')) {
            privateKey = privateKey.substr(2);
        }
        return privateKey;
    };
    ChainNotaryEvm.addOx = function (privateKey) {
        if (!privateKey.startsWith('0x'))
            privateKey = '0x' + privateKey;
        return privateKey;
    };
    ChainNotaryEvm.prototype.balanceOf = function () {
        return __awaiter(this, void 0, void 0, function () {
            var subProvider, managerArgs, contract, _a;
            return __generator(this, function (_b) {
                switch (_b.label) {
                    case 0:
                        subProvider = this.getWeb3Subprovider();
                        managerArgs = this.manager.arguments;
                        contract = new subProvider.Contract(ERC20, this.manager.arguments.contract, { gasLimit: 1000000 });
                        _a = this;
                        return [4 /*yield*/, contract.methods.balanceOf(this.manager.arguments.to).call().then(function (data) {
                                return { address: managerArgs.to, balance: data };
                            })];
                    case 1:
                        _a.response = _b.sent();
                        _super.prototype.balanceOf.call(this);
                        return [2 /*return*/];
                }
            });
        });
    };
    ChainNotaryEvm.prototype.ownerOf = function () {
        return __awaiter(this, void 0, void 0, function () {
            var subProvider, contract;
            return __generator(this, function (_a) {
                subProvider = this.getWeb3Subprovider();
                contract = subProvider.Contract(ERC721, this.manager.arguments.contract, { gasLimit: 1000000 });
                console.log(this.manager.arguments.tokenPath.tokenId);
                console.log(this.manager.arguments.tokenPath);
                contract.methods.ownerOf(this.manager.arguments.tokenPath.tokenId).call().then(console.log);
                return [2 /*return*/];
            });
        });
    };
    ChainNotaryEvm.prototype.mintTo = function () {
        return __awaiter(this, void 0, void 0, function () {
            var subProvider, sender, manager, contract;
            var _this = this;
            return __generator(this, function (_a) {
                subProvider = this.getWeb3Subprovider();
                sender = subProvider.accounts.wallet.add(this.convertPrivateKey(this.manager.arguments.key));
                manager = this.manager;
                contract = new subProvider.Contract(MINTABLE, this.manager.arguments.contract, { gasLimit: 1000000 });
                contract.methods.mintTo(this.manager.arguments.to).send({ from: sender.address, gas: '3000000' }).then(function (value) {
                    _this.response = value;
                }).catch(function (e) {
                    var response = { error: e.message };
                    console.log(e.message);
                    manager.returnError(response);
                }).then(function () {
                    _super.prototype.send.call(_this);
                });
                return [2 /*return*/];
            });
        });
    };
    ChainNotaryEvm.prototype.contractStandardRouting = function (contractAddress, web3Internal) {
        if (typeof contractAddress == "undefined")
            return null;
        var ABI = ERC20;
        this.contractInterface = ContractInterface.IERC20;
        if (this.manager.arguments.tokenPath && this.manager.arguments.tokenPath.tokenId) {
            ABI = ERC721;
            this.contractInterface = ContractInterface.IERC721;
        }
        return new web3Internal.Contract(ABI, contractAddress, { gasLimit: 1000000 });
    };
    ChainNotaryEvm.prototype.sendMethodRouting = function (contractObject, receivingAddress) {
        var data = '';
        if (contractObject == null)
            return data;
        switch (this.contractInterface) {
            case ContractInterface.IERC20:
                data = contractObject.methods.transfer(receivingAddress, this.manager.arguments.quantity).encodeABI();
                break;
            case ContractInterface.IERC721:
                data = contractObject.methods.transferFrom(this.manager.arguments.from, receivingAddress, this.manager.arguments.tokenPath.tokenId).encodeABI();
                break;
            default:
                this.manager.returnError("Cant define contract interface for send");
        }
        return data;
    };
    ChainNotaryEvm.prototype.createWallet = function () {
        var subProvider = this.getWeb3Subprovider();
        var account = subProvider.accounts.create('9jfirt43nfdslgjt43ofgj5gunvmdiwere');
        var response = { address: account.address, privateKey: account.privateKey };
        this.response = response;
        return _super.prototype.createWallet.call(this);
    };
    ChainNotaryEvm.prototype.chainParseTransactions = function () {
        var subprovider = this.getWeb3Subprovider();
        // @ts-ignore
        var contract = new subprovider.Contract(ERC721, this.manager.arguments.contract, { gasLimit: 1000000 });
        var chainParser = new ChainParser();
        chainParser.blockInterval = 10;
        var bootData = { fromBlock: 27991852, contract: contract };
        // bootData.toBlock = 27991853 ;
        //node CSNotary/NotaryExecutor.js --chain=klaytn --command=parseContractEvents --contract=0xe3656452c8238334efdfc811d6f98e5962fe4461
        chainParser.parseAndSendContractTransaction(this, bootData).then(function (r) { return console.log("sdf"); });
    };
    ChainNotaryEvm.prototype.getContractData = function () {
        return __awaiter(this, void 0, void 0, function () {
            var subprovider, validation, validERC20, decimals, contract, symbol, name, err_1, error, response;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        subprovider = this.getWeb3Subprovider();
                        return [4 /*yield*/, this.manager.validateInputs(['contract'])];
                    case 1:
                        validation = _a.sent();
                        validERC20 = true;
                        decimals = 0;
                        contract = null;
                        symbol = null;
                        name = null;
                        _a.label = 2;
                    case 2:
                        _a.trys.push([2, 6, , 7]);
                        // @ts-ignore
                        contract = new subprovider.Contract(ERC20, this.manager.arguments.contract, { gasLimit: 1000000 });
                        return [4 /*yield*/, contract.methods.decimals().call().then(function (data) { return data; })];
                    case 3:
                        decimals = _a.sent();
                        return [4 /*yield*/, contract.methods.symbol().call().then(function (data) { return data; })];
                    case 4:
                        symbol = _a.sent();
                        return [4 /*yield*/, contract.methods.name().call().then(function (data) { return data; })];
                    case 5:
                        name = _a.sent();
                        if (decimals == undefined) {
                            this.response = 'not ERC-20 compatible';
                            this.manager.returnError(this.response);
                            return [2 /*return*/];
                        }
                        return [3 /*break*/, 7];
                    case 6:
                        err_1 = _a.sent();
                        validERC20 = false;
                        error = 'not ERC-20 compatible';
                        this.manager.returnError(error);
                        return [2 /*return*/];
                    case 7:
                        response = { decimals: decimals, symbol: symbol, name: name, interfaces: ['ERC20'] };
                        this.response = response;
                        _super.prototype.send.call(this);
                        return [2 /*return*/];
                }
            });
        });
    };
    ChainNotaryEvm.prototype.broadcast = function () {
        return __awaiter(this, void 0, void 0, function () {
            var response, txString, _a, err_2;
            return __generator(this, function (_b) {
                switch (_b.label) {
                    case 0:
                        response = {};
                        txString = this.manager.arguments.tx.toString();
                        _b.label = 1;
                    case 1:
                        _b.trys.push([1, 3, , 4]);
                        _a = response;
                        return [4 /*yield*/, this.waitForHash(txString)];
                    case 2:
                        _a.txHash = _b.sent();
                        return [3 /*break*/, 4];
                    case 3:
                        err_2 = _b.sent();
                        response.error = err_2.message;
                        this.response = response;
                        this.manager.returnError(response);
                        return [2 /*return*/];
                    case 4:
                        this.response = response;
                        _super.prototype.send.call(this);
                        return [2 /*return*/];
                }
            });
        });
    };
    ChainNotaryEvm.prototype.createSend = function () {
        return __awaiter(this, void 0, void 0, function () {
            var web3Instance, subProvider, validation, CONTRACT_ADDRESS, RECEIVER_ADDRESS, SENDER_ADDRESS, gasPriceGwei, contract, quantity, count, gasLimit, rawTransaction, e_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        web3Instance = this.getWeb3Provider();
                        subProvider = this.getWeb3Subprovider();
                        return [4 /*yield*/, this.manager.validateInputs(['from', 'to'])];
                    case 1:
                        validation = _a.sent();
                        CONTRACT_ADDRESS = this.manager.arguments.contract;
                        RECEIVER_ADDRESS = this.manager.arguments.to;
                        SENDER_ADDRESS = this.manager.arguments.from;
                        gasPriceGwei = null;
                        if (!this.manager.arguments.fee) return [3 /*break*/, 2];
                        gasPriceGwei = web3.utils.toHex(this.manager.arguments.fee * 1e9);
                        return [3 /*break*/, 4];
                    case 2: return [4 /*yield*/, subProvider.getGasPrice()];
                    case 3:
                        gasPriceGwei = _a.sent();
                        _a.label = 4;
                    case 4:
                        contract = this.contractStandardRouting(CONTRACT_ADDRESS, subProvider);
                        quantity = "0x0";
                        if (!contract)
                            quantity = web3.utils.toHex(this.manager.arguments.quantity);
                        return [4 /*yield*/, subProvider.getTransactionCount(SENDER_ADDRESS, 'pending')];
                    case 5:
                        count = _a.sent();
                        gasLimit = 200000;
                        rawTransaction = {
                            "from": SENDER_ADDRESS,
                            "gasPrice": web3.utils.toHex(gasPriceGwei),
                            //"gasPrice": web3.utils.toHex(gasPriceGwei * 1e9),
                            "gasLimit": web3.utils.toHex(gasLimit),
                            "to": CONTRACT_ADDRESS !== null && CONTRACT_ADDRESS !== void 0 ? CONTRACT_ADDRESS : RECEIVER_ADDRESS,
                            "value": quantity,
                            "data": this.sendMethodRouting(contract, RECEIVER_ADDRESS),
                            'nonce': web3.utils.toHex(count),
                            'chainId': web3.utils.toHex(this.chainId)
                        };
                        _a.label = 6;
                    case 6:
                        _a.trys.push([6, 8, , 9]);
                        return [4 /*yield*/, subProvider.estimateGas(rawTransaction)];
                    case 7:
                        gasLimit = _a.sent();
                        return [3 /*break*/, 9];
                    case 8:
                        e_1 = _a.sent();
                        this.manager.returnError(e_1.message);
                        return [2 /*return*/];
                    case 9:
                        rawTransaction.gasLimit = web3.utils.toHex(gasLimit);
                        this.response = rawTransaction;
                        _super.prototype.createSend.call(this);
                        return [2 /*return*/];
                }
            });
        });
    };
    ChainNotaryEvm.prototype.estimateGas = function (contractObject, receivingAddress) {
        var gasRequirement = null;
        switch (this.contractInterface) {
            case ContractInterface.IERC20:
                gasRequirement = contractObject.methods.transfer.estimateGas(receivingAddress, this.manager.arguments.quantity);
                break;
            case ContractInterface.IERC721:
                gasRequirement = contractObject.methods.transferFrom.estimateGas(this.manager.arguments.from, receivingAddress, this.manager.arguments.tokenPath.tokenId);
                break;
        }
    };
    ChainNotaryEvm.prototype.waitForHash = function (signedTx) {
        var _this = this;
        return new Promise(function (resolve, reject) {
            var web3Instance = _this.getWeb3Subprovider();
            web3Instance.sendSignedTransaction(signedTx)
                .once('transactionHash', function (hash) {
                resolve(hash);
            }).on('error', function (err) {
                reject(err);
            });
        });
    };
    ChainNotaryEvm.prototype.callContractMethod = function (contract) {
        return __awaiter(this, void 0, void 0, function () {
            var _this = this;
            return __generator(this, function (_a) {
                return [2 /*return*/, new Promise(function (resolve, reject) {
                        var web3Instance = _this.getWeb3Subprovider();
                        var decimals = contract.methods.decimals().call().then(function (data) { resolve(data); }, function (e) { reject(e); }).catch(function (error) {
                        });
                    })];
            });
        });
    };
    return ChainNotaryEvm;
}(ChainNotaryBase));
export { ChainNotaryEvm };
var ContractInterface;
(function (ContractInterface) {
    ContractInterface["IERC20"] = "IERC20";
    ContractInterface["IERC721"] = "IERC721";
})(ContractInterface || (ContractInterface = {}));
