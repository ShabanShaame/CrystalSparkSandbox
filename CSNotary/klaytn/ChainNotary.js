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
var Caver = require('caver-js');
require('dotenv').config({ path: '../../../' });
var Tx = require('ethereumjs-tx').Transaction;
var ChainNotary = /** @class */ (function (_super) {
    __extends(ChainNotary, _super);
    function ChainNotary() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.OxOnPrivateKey = true;
        return _this;
    }
    ChainNotary.prototype.getWeb3Provider = function () {
        var web3Instance = new Caver(new Caver.providers.HttpProvider("https://cypress.en.klayfee.com/"));
        return web3Instance;
    };
    ChainNotary.prototype.getWeb3Subprovider = function () {
        var web3 = this.getWeb3Provider();
        return web3.klay;
    };
    ChainNotary.prototype.send = function () {
        return __awaiter(this, void 0, void 0, function () {
            var subProvider, Caver, caver, sender, payer, CONTRACT_ADDRESS, RECEIVER_ADDRESS, subProvider2, contract, senderRawTransaction, response;
            var _this = this;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        subProvider = this.getWeb3Subprovider();
                        Caver = require('caver-js');
                        caver = new Caver('https://cypress.en.klayfee.com');
                        require('dotenv').config();
                        sender = caver.klay.accounts.wallet.add(this.convertPrivateKey(this.manager.arguments.key));
                        payer = caver.klay.accounts.wallet.add(this.convertPrivateKey(this.manager.arguments.feePayerPrivate), this.manager.arguments.feePayerPublic);
                        CONTRACT_ADDRESS = this.manager.arguments.contract;
                        RECEIVER_ADDRESS = this.manager.arguments.to;
                        subProvider2 = caver.klay;
                        contract = this.contractStandardRouting(CONTRACT_ADDRESS, subProvider);
                        return [4 /*yield*/, subProvider.accounts.signTransaction({
                                type: 'FEE_DELEGATED_SMART_CONTRACT_EXECUTION',
                                from: sender.address,
                                to: CONTRACT_ADDRESS,
                                data: this.sendMethodRouting(contract, RECEIVER_ADDRESS),
                                gas: '3000000',
                                value: 0,
                            }, sender.privateKey)];
                    case 1:
                        senderRawTransaction = (_a.sent()).rawTransaction;
                        // signed raw transaction
                        console.log("Raw TX:\n", senderRawTransaction);
                        response = {};
                        // send fee delegated transaction with fee payer information
                        //For an unknow reason subprovider.sendTransaction return an error we have to reinitial caver.klay to make it work
                        caver.klay.sendTransaction({
                            senderRawTransaction: senderRawTransaction,
                            feePayer: payer.address
                        })
                            .on('transactionHash', function (hash) {
                            console.log(">>> tx_hash for deploy =", hash);
                            response.txHash = hash;
                        })
                            .on('receipt', function (receipt) {
                            console.log(">>> receipt arrived: ", receipt);
                            response.receipt = receipt;
                        })
                            .on('error', function (err) {
                            console.error(">>> error: ", err);
                        }).then(function (value) {
                            _this.response = response;
                            _super.prototype.send.call(_this);
                        });
                        return [2 /*return*/];
                }
            });
        });
    };
    return ChainNotary;
}(ChainNotary_1.ChainNotaryEvm));
exports.ChainNotary = ChainNotary;
//# sourceMappingURL=ChainNotary.js.map