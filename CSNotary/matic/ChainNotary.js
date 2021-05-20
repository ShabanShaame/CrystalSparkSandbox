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
import { ChainNotaryEvm } from "../evm/ChainNotary";
var web3 = require('web3');
require('dotenv').config();
var Tx = require('ethereumjs-tx');
var ChainNotary = /** @class */ (function (_super) {
    __extends(ChainNotary, _super);
    function ChainNotary() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    ChainNotary.prototype.getWeb3Provider = function () {
        var web3Instance = new web3(new web3.providers.HttpProvider("https://testnetv3.matic.network"));
        return web3Instance;
    };
    ChainNotary.prototype.getWeb3Subprovider = function () {
        var web3 = this.getWeb3Provider();
        return web3.eth;
    };
    ChainNotary.prototype.send = function () {
        return __awaiter(this, void 0, void 0, function () {
            var web3Instance, validation, CONTRACT_ADDRESS, RECEIVER_ADDRESS, privateKey, account, sender, contract, count, gasPriceGwei, gasLimit, rawTransaction, tx, privKey, serializedTx;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        web3Instance = this.getWeb3Provider();
                        return [4 /*yield*/, this.manager.validateInputs(['from', 'contract', 'to', 'key', 'quantity'])];
                    case 1:
                        validation = _a.sent();
                        CONTRACT_ADDRESS = this.manager.arguments.contract;
                        RECEIVER_ADDRESS = this.manager.arguments.to;
                        privateKey = this.manager.arguments.key;
                        account = web3Instance.eth.accounts.privateKeyToAccount('0x' + privateKey);
                        sender = web3Instance.eth.accounts.wallet.add(account);
                        web3Instance.eth.defaultAccount = account.address;
                        contract = this.contractStandardRouting(CONTRACT_ADDRESS, web3Instance.eth);
                        return [4 /*yield*/, web3Instance.eth.getTransactionCount(sender.address)];
                    case 2:
                        count = _a.sent();
                        gasPriceGwei = 60;
                        gasLimit = 200000;
                        rawTransaction = {
                            "from": sender.address,
                            "gasPrice": web3.utils.toHex(gasPriceGwei * 1e9),
                            "gasLimit": web3.utils.toHex(gasLimit),
                            "to": CONTRACT_ADDRESS,
                            "value": "0x0",
                            "data": this.sendMethodRouting(contract, RECEIVER_ADDRESS),
                            'nonce': web3.utils.toHex(count)
                        };
                        console.log("sender" + sender.address);
                        tx = new Tx(rawTransaction);
                        privKey = Buffer.from(this.manager.arguments.key, 'hex');
                        tx.sign(privKey);
                        serializedTx = tx.serialize();
                        web3Instance.eth.sendSignedTransaction('0x' + serializedTx.toString('hex')).on('transactionHash', function (hash) {
                            console.log(">>> tx_hash for deploy =", hash);
                        }).on('receipt', function (receipt) {
                            console.log(">>> receipt arrived: ", receipt);
                        })
                            .on('error', function (err) {
                            console.error(">>> error: ", err);
                        });
                        return [2 /*return*/];
                }
            });
        });
    };
    return ChainNotary;
}(ChainNotaryEvm));
export { ChainNotary };
