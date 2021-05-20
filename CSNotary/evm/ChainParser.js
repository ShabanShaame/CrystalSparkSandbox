"use strict";
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
exports.ChainParser = void 0;
//this is to count how many transaction are sent back from the blockchain node
var countNodeResponses;
var ChainParser = /** @class */ (function () {
    function ChainParser() {
        this.blockInterval = 5000;
        this.actualInterval = 5000;
        this.countLastEvents = 0;
        this.retreiveBlockTimestamp = true; //a true create a significant amount of call to the node. To use when not rate limited
    }
    ChainParser.prototype.parseAndSendContractTransaction = function (notary, bootData) {
        return __awaiter(this, void 0, void 0, function () {
            var toBlock, latest, currentBlock, iteration;
            var _this = this;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        toBlock = bootData.fromBlock + this.blockInterval;
                        this.contract = bootData.contract;
                        this.subProvider = notary.getWeb3Subprovider();
                        return [4 /*yield*/, notary.getWeb3Subprovider().getBlockNumber()];
                    case 1:
                        latest = _a.sent();
                        if (bootData.toBlock)
                            latest = bootData.toBlock;
                        if (toBlock > bootData.toBlock)
                            toBlock = bootData.toBlock;
                        currentBlock = bootData.fromBlock;
                        iteration = 0;
                        countNodeResponses = 0;
                        _a.label = 2;
                    case 2:
                        if (!(currentBlock < latest)) return [3 /*break*/, 5];
                        iteration++;
                        return [4 /*yield*/, notary.sleep(200)];
                    case 3:
                        _a.sent();
                        if (iteration > 40000) {
                            console.log("Should break");
                            return [3 /*break*/, 5];
                        }
                        ;
                        console.log("iteration" + iteration);
                        console.log("Number of last events" + countNodeResponses);
                        return [4 /*yield*/, this.parse({ fromBlock: currentBlock, toBlock: toBlock }, notary).then(function (value) {
                                if (countNodeResponses < 10000)
                                    _this.actualInterval = _this.actualInterval * 2;
                                if (countNodeResponses > 50000) {
                                    _this.actualInterval = _this.actualInterval / 2;
                                    //we had too many events let's cool it down
                                    console.log("Cooling block interval to " + _this.actualInterval);
                                }
                                currentBlock = toBlock;
                                toBlock = toBlock + _this.actualInterval;
                                toBlock = Math.floor(toBlock);
                                console.log("intervalling" + _this.actualInterval);
                            }).catch(function (error) {
                                _this.actualInterval = _this.actualInterval / 6;
                                toBlock = currentBlock - _this.actualInterval;
                                toBlock = Math.floor(toBlock);
                                console.log(error);
                                console.log("Promise reject slashing interval to " + _this.actualInterval);
                            })];
                    case 4:
                        _a.sent();
                        return [3 /*break*/, 2];
                    case 5: return [2 /*return*/];
                }
            });
        });
    };
    ChainParser.prototype.parse = function (data, notary) {
        return __awaiter(this, void 0, void 0, function () {
            function dispatchTransactions(transactions) {
                return __awaiter(this, void 0, void 0, function () {
                    var axios;
                    return __generator(this, function (_a) {
                        switch (_a.label) {
                            case 0:
                                axios = require('axios').default;
                                return [4 /*yield*/, axios.post('http://localhost:8888/crystalcontrolcenter/public/api/v1/events/klaytn/import', transactions.list)
                                        //const response = await axios.post('http://baster.bitcrystals.com/api/v1/events/import', transactions.list)
                                        .then(function (response) {
                                        console.log(response.data);
                                        console.log("dataPushed");
                                    })
                                        .catch(function (error) {
                                        console.log(error.response.data.message);
                                        console.log("stopping all");
                                        process.exit();
                                    })];
                            case 1: return [2 /*return*/, _a.sent()];
                        }
                    });
                });
            }
            function getBlocktime(web3Provider, blockId) {
                return __awaiter(this, void 0, void 0, function () {
                    var timestamp;
                    return __generator(this, function (_a) {
                        switch (_a.label) {
                            case 0:
                                timestamp = web3Provider.getBlock(blockId, function (error, block) {
                                    if (!error) {
                                        timestamp = block.timestamp;
                                    }
                                }).catch(function (error) {
                                    console.log("time is to fast");
                                    throw error;
                                }).then(function (r) {
                                    return timestamp;
                                });
                                return [4 /*yield*/, timestamp];
                            case 1: return [2 /*return*/, _a.sent()];
                        }
                    });
                });
            }
            var blockTimestampCall, transactions, parse;
            var _this = this;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        console.log("starting transaction");
                        console.log(data);
                        blockTimestampCall = this.retreiveBlockTimestamp;
                        transactions = this.transactions;
                        return [4 /*yield*/, this.contract.getPastEvents('Transfer', {
                                fromBlock: data.fromBlock,
                                toBlock: data.toBlock
                            }, function (error, events) {
                                return __awaiter(this, void 0, void 0, function () {
                                    return __generator(this, function (_a) {
                                        return [2 /*return*/];
                                    });
                                });
                            }).then(function (r) { return __awaiter(_this, void 0, void 0, function () {
                                function process() {
                                    return __awaiter(this, void 0, void 0, function () {
                                        var countEvents, processed, _loop_1, _i, r_1, event_1;
                                        var _this = this;
                                        return __generator(this, function (_a) {
                                            switch (_a.label) {
                                                case 0:
                                                    countEvents = r.length;
                                                    countNodeResponses = countEvents;
                                                    processed = 0;
                                                    transactions = new TransactionList();
                                                    console.log("countEvent " + countEvents);
                                                    _loop_1 = function (event_1) {
                                                        var getTimestamp;
                                                        return __generator(this, function (_a) {
                                                            switch (_a.label) {
                                                                case 0:
                                                                    getTimestamp = function () { return __awaiter(_this, void 0, void 0, function () {
                                                                        return __generator(this, function (_a) {
                                                                            switch (_a.label) {
                                                                                case 0:
                                                                                    //  console.log('Taking a break...');
                                                                                    // console.log('Two seconds later, showing sleep in a loop...');
                                                                                    // console.log("Ouh oh we should sleep")
                                                                                    // await sleep(1);
                                                                                    //if (processed % 10 == 0)
                                                                                    //console.log("enoughSleep"+processed)
                                                                                    if (!blockTimestampCall)
                                                                                        return [2 /*return*/, 1];
                                                                                    return [4 /*yield*/, getBlocktime(notary.getWeb3Subprovider(), event_1.blockNumber)];
                                                                                case 1: return [2 /*return*/, _a.sent()];
                                                                            }
                                                                        });
                                                                    }); };
                                                                    // or
                                                                    return [4 /*yield*/, getTimestamp().catch(function (error) {
                                                                            console.log("Timestamp rejected");
                                                                            throw (error);
                                                                        }).then(function (value) { return __awaiter(_this, void 0, void 0, function () {
                                                                            var token, done;
                                                                            return __generator(this, function (_a) {
                                                                                switch (_a.label) {
                                                                                    case 0:
                                                                                        processed++;
                                                                                        if (event_1.returnValues == true)
                                                                                            return [2 /*return*/]; //what is this
                                                                                        token = [];
                                                                                        token['tokenId'] = event_1.returnValues.tokenId;
                                                                                        console.log(event_1.returnValues);
                                                                                        transactions.addTransaction({
                                                                                            tokenId: event_1.returnValues.tokenId,
                                                                                            source: event_1.returnValues.from,
                                                                                            contract: event_1.address,
                                                                                            destination: event_1.returnValues.to,
                                                                                            txHash: event_1.transactionHash,
                                                                                            transactionIndex: event_1.transactionIndex,
                                                                                            blockIndex: event_1.blockNumber,
                                                                                            blockTime: value
                                                                                        });
                                                                                        if (!(countEvents == processed)) return [3 /*break*/, 2];
                                                                                        return [4 /*yield*/, dispatchTransactions(transactions)];
                                                                                    case 1:
                                                                                        done = _a.sent();
                                                                                        _a.label = 2;
                                                                                    case 2: return [2 /*return*/];
                                                                                }
                                                                            });
                                                                        }); })];
                                                                case 1:
                                                                    // or
                                                                    _a.sent();
                                                                    return [2 /*return*/];
                                                            }
                                                        });
                                                    };
                                                    _i = 0, r_1 = r;
                                                    _a.label = 1;
                                                case 1:
                                                    if (!(_i < r_1.length)) return [3 /*break*/, 4];
                                                    event_1 = r_1[_i];
                                                    return [5 /*yield**/, _loop_1(event_1)];
                                                case 2:
                                                    _a.sent();
                                                    _a.label = 3;
                                                case 3:
                                                    _i++;
                                                    return [3 /*break*/, 1];
                                                case 4: return [2 /*return*/];
                                            }
                                        });
                                    });
                                }
                                var processResult;
                                return __generator(this, function (_a) {
                                    switch (_a.label) {
                                        case 0: return [4 /*yield*/, process().catch(function (error) {
                                                //this.actualInterval = this.actualInterval /10
                                                console.log("error while processing");
                                                throw (error);
                                            })
                                                .then(function (value) {
                                                console.log("should be all finished" + value);
                                            })];
                                        case 1:
                                            processResult = _a.sent();
                                            return [2 /*return*/];
                                    }
                                });
                            }); }).catch(function (error) {
                                console.log("too much");
                                throw error;
                            })];
                    case 1:
                        parse = _a.sent();
                        return [2 /*return*/];
                }
            });
        });
    };
    return ChainParser;
}());
exports.ChainParser = ChainParser;
var http = require('http');
var https = require('https');
var TransactionList = /** @class */ (function () {
    function TransactionList() {
        this.list = [];
    }
    TransactionList.prototype.addTransaction = function (transaction) {
        // if (!transaction.tokenId || !transaction.source ||!transaction.destination){ return ;}
        this.list.push(transaction);
    };
    return TransactionList;
}());
var options = /** @class */ (function () {
    function options() {
        this.retreiveBlockTimestamp = false;
    }
    return options;
}());
//# sourceMappingURL=ChainParser.js.map