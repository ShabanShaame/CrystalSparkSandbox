var ChainNotaryBase = /** @class */ (function () {
    function ChainNotaryBase(manager) {
        this.manager = null;
        this.response = "unsuported method";
        this.legacyResponseType = true;
        this.manager = manager;
    }
    ChainNotaryBase.prototype.flatTokenPath = function () {
    };
    ChainNotaryBase.prototype.send = function () {
        var lastLine = null;
        if (this.legacyResponseType)
            lastLine = this.response.balance;
        this.manager.returnResponse(this.response, lastLine);
    };
    ChainNotaryBase.prototype.createSend = function () {
        var lastLine = null;
        if (this.legacyResponseType)
            lastLine = this.response.balance;
        this.manager.returnResponse(this.response, lastLine);
    };
    ChainNotaryBase.prototype.balanceOf = function () {
        console.log(this.response);
        var lastLine = null;
        if (this.legacyResponseType)
            lastLine = this.response.balance;
        this.manager.returnResponse(this.response, lastLine);
    };
    ChainNotaryBase.prototype.chainParseTransactions = function () {
        console.log(this.response);
        this.manager.returnResponse(this.response, this.response.balance);
    };
    ChainNotaryBase.prototype.ownerOf = function () {
    };
    ChainNotaryBase.prototype.getPendingTransactions = function () {
        this.manager.returnResponse(this.response, this.response.balance);
    };
    ChainNotaryBase.prototype.createWallet = function () {
        this.manager.returnResponse(this.response, false);
    };
    ChainNotaryBase.prototype.mintTo = function () {
        this.manager.returnResponse(this.response, false);
    };
    ChainNotaryBase.prototype.broadcast = function () {
        this.manager.returnResponse(this.response, false);
    };
    ChainNotaryBase.prototype.sleep = function (ms) {
        return new Promise(function (resolve) { return setTimeout(resolve, ms); });
    };
    return ChainNotaryBase;
}());
export { ChainNotaryBase };
