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
var ChainNotaryBase_1 = require("../ChainNotaryBase");
require('dotenv').config();
var exec = require("child_process").exec;
var ChainNotary = /** @class */ (function (_super) {
    __extends(ChainNotary, _super);
    function ChainNotary() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    ChainNotary.prototype.broadcast = function () {
        return __awaiter(this, void 0, void 0, function () {
            var validation, txString, me;
            var _this = this;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.manager.validateInputs(['tx'])];
                    case 1:
                        validation = _a.sent();
                        txString = this.manager.arguments.tx.toString();
                        return [4 /*yield*/, exec("php " + __dirname + "/broadcast.php --tx='" + txString + "'", function (error, stdout, stderr) {
                                if (error) {
                                    console.log("error: " + error.message);
                                }
                                if (stderr) {
                                    console.log("stderr: " + stderr);
                                }
                                // console.log(`stdout: ${stdout}`);
                                // process.exit();
                                var xcpResponse = JSON.parse(stdout);
                                if (xcpResponse.status == 'error')
                                    _this.manager.returnError(xcpResponse);
                                if (xcpResponse.error)
                                    _this.manager.returnError(xcpResponse);
                                _this.response = xcpResponse;
                                _super.prototype.broadcast.call(_this);
                            })];
                    case 2:
                        me = _a.sent();
                        return [2 /*return*/];
                }
            });
        });
    };
    ChainNotary.prototype.send = function () {
        return __awaiter(this, void 0, void 0, function () {
            var validation, query;
            var _this = this;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.manager.validateInputs(['from', 'contract', 'to', 'key', 'quantity'])];
                    case 1:
                        validation = _a.sent();
                        query = { from: this.manager.arguments.from, to: this.manager.arguments.to, contract: this.manager.arguments.contract, key: this.manager.arguments.key, quantity: this.manager.arguments.quantity };
                        exec("php " + __dirname + "/send.php --query='" + JSON.stringify(query) + "'", function (error, stdout, stderr) {
                            if (error) {
                                console.log("error: " + error.message);
                                return;
                            }
                            if (stderr) {
                                console.log("stderr: " + stderr);
                                return;
                            }
                            // console.log(`stdout: ${stdout}`);
                            // process.exit();
                            var xcpResponse = JSON.parse(stdout);
                            if (xcpResponse.status == 'error')
                                _this.manager.returnError(xcpResponse);
                            if (xcpResponse.error)
                                _this.manager.returnError(xcpResponse);
                            _this.response = xcpResponse;
                            _super.prototype.send.call(_this);
                        });
                        return [2 /*return*/];
                }
            });
        });
    };
    ChainNotary.prototype.createWallet = function () {
        var _this = this;
        exec("php " + __dirname + "/create_wallet.php", function (error, stdout, stderr) {
            if (error) {
                _this.manager.returnError(error.message);
                // console.log(`error: ${error.message}`);
                return;
            }
            if (stderr) {
                _this.manager.returnError(stderr);
                return;
            }
            //console.log(`stdout: ${stdout}`);
            var reponseObj = JSON.parse(stdout);
            _this.response = reponseObj;
            _super.prototype.createWallet.call(_this);
        });
    };
    return ChainNotary;
}(ChainNotaryBase_1.ChainNotaryBase));
exports.ChainNotary = ChainNotary;
//# sourceMappingURL=ChainNotary.js.map