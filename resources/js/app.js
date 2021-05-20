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
// import {CsCannonApiManager} from "./sandra/src/CSCannon/CsCannonApiManager.js";
// import {CSCanonizeManager} from "./sandra/src/CSCannon/CSCanonizeManager.js";
// import {EntityFactoryToView} from "./EntityFactoryToView.js";
import { Reference } from "canonizer/src/Reference.js";
import { CSCanonizeManager } from "canonizer/src/canonizer/CSCanonizeManager";
import { CsCannonApiManager } from "canonizer/src/canonizer/CsCannonApiManager";
import { EntityFactoryToView } from "./EntityFactoryToView.js";
var App = /** @class */ (function () {
    function App() {
        var canonizeManager = new CSCanonizeManager();
        var apiUrl = 'https://ksmjetski.everdreamsoft.com/';
        try {
            // @ts-ignore
            var cookieUrl = getCookie('apiUrl');
            if (cookieUrl) {
                apiUrl = cookieUrl;
            }
        }
        catch (e) {
        }
        this.apiManager = new CsCannonApiManager(canonizeManager, apiUrl);
        console.log("app loaded --");
        EntityFactoryToView.viewInit();
    }
    App.prototype.displayCollections = function () {
        return __awaiter(this, void 0, void 0, function () {
            var collections, factory;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.apiManager.getCollections()];
                    case 1:
                        collections = _a.sent();
                        if (collections.length > 0) {
                            factory = collections[0].factory;
                            EntityFactoryToView.factoryToView(factory, 'collectionFactory');
                        }
                        return [2 /*return*/];
                }
            });
        });
    };
    App.prototype.displayAssets = function (collectionId) {
        return __awaiter(this, void 0, void 0, function () {
            var assetsFactory;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.apiManager.getCollectionAssets(collectionId)];
                    case 1:
                        assetsFactory = _a.sent();
                        EntityFactoryToView.factoryToView(assetsFactory, 'assetFactory');
                        return [2 /*return*/];
                }
            });
        });
    };
    App.prototype.displayCollectionEvents = function (collectionId) {
        return __awaiter(this, void 0, void 0, function () {
            var eventFactory;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.apiManager.getCollectionEvents(collectionId)];
                    case 1:
                        eventFactory = _a.sent();
                        EntityFactoryToView.factoryToView(eventFactory, 'eventFactory');
                        return [2 /*return*/, eventFactory];
                }
            });
        });
    };
    App.prototype.displayAddressEvents = function (address) {
        return __awaiter(this, void 0, void 0, function () {
            var assetsFactory;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.apiManager.getAddressEvents(address)];
                    case 1:
                        assetsFactory = _a.sent();
                        EntityFactoryToView.factoryToView(assetsFactory, 'eventFactory');
                        return [2 /*return*/];
                }
            });
        });
    };
    App.prototype.displayBalance = function (address) {
        return __awaiter(this, void 0, void 0, function () {
            var assetsFactory;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.apiManager.getAddressBalance(address)];
                    case 1:
                        assetsFactory = _a.sent();
                        EntityFactoryToView.factoryToView(assetsFactory, 'assetFactory');
                        return [2 /*return*/];
                }
            });
        });
    };
    App.prototype.displayLastActiveAccounts = function () {
        return __awaiter(this, void 0, void 0, function () {
            var eventFactory;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        console.log(this.apiManager);
                        return [4 /*yield*/, this.apiManager.getEvents()];
                    case 1:
                        eventFactory = _a.sent();
                        eventFactory.entityArray = eventFactory.entityArray.slice(0, 10);
                        eventFactory.entityArray.forEach(function (entity) {
                            var sourceAddress = entity.getJoinedEntitiesOnVerb('source')[0];
                            var sourceAddressString = sourceAddress.getRefValue('address');
                            var activeAddressString = sourceAddressString;
                            if (sourceAddressString == '0x0000000000000000000000000000000000000000' ||
                                sourceAddressString == '0x0') {
                                var destAddress = entity.getJoinedEntitiesOnVerb('hasSingleDestination')[0];
                                activeAddressString = destAddress.getRefValue('address');
                            }
                            entity.addReference(new Reference(entity.factory.sandraManager.get('active'), activeAddressString));
                        });
                        EntityFactoryToView.factoryToView(eventFactory, 'recentAddressFactory');
                        return [2 /*return*/, eventFactory];
                }
            });
        });
    };
    App.prototype.displayLatestAssets = function (eventFactory) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                EntityFactoryToView.factoryToView(eventFactory, 'recentAssetFactory');
                return [2 /*return*/];
            });
        });
    };
    return App;
}());
export { App };
var app = new App();
// @ts-ignore
app.displayBalance('DMs4NstKrNkutq6A2wLLMTeQLZZUgbpEYbZeG2cmW1HampZ').then(lazyload());
function lazyload() {
    //we should remove from here
    // @ts-ignore
    $('.lazy').Lazy({
        visibleOnly: true,
        // @ts-ignore
        onError: function (element) {
            var imageSrc = element.data('src');
            console.log('image "' + imageSrc + '" could not be loaded');
        }
    }); //reload lazy
    console.log("The real lazy load");
}
