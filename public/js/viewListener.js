import { App } from "./app.js";
import { CSCanonizeManager } from "../node_module/canonizer/src/canonizer/CSCanonizeManager";
var app = new App();
// @ts-ignore
if (page === 'home') {
    app.displayCollections().then(function (r) { lazyload(); });
    app.displayLastActiveAccounts().then(function (eventFactory) {
        console.log("we got event factory");
        app.displayLatestAssets(eventFactory).then(function (eventFactory) { convertTimestamp(); });
    });
}
// @ts-ignore
if (page === 'assetList') {
    var collectionId = findGetParameter('collectionId');
    app.displayAssets(collectionId).then(function (r) { console.log("asset loaded"); lazyload(); });
    app.displayCollectionEvents(collectionId).then(function (r) {
        console.log("transactions loaded");
        convertTimestamp();
        removeMintLink();
        lazyload();
    });
}
// @ts-ignore
if (page === 'addressBalance') {
    var address = findGetParameter('address');
    displayAddress(address);
    app.displayBalance(address).then(function (r) { console.log("balance loaded"); });
    app.displayAddressEvents(address).then(function (r) {
        console.log("transactions loaded");
        convertTimestamp();
        removeMintLink();
        lazyload();
    });
}
$(document).on('click', '.collectionLink', function (e) {
    e.preventDefault();
    // similar behavior as clicking on a link
    window.location.href = "galleryAssetList.html?collectionId=" + $(this).attr('data-sandra-ref-collectionid');
});
$(document).on('click', '.addressLink', function (e) {
    e.preventDefault();
    var targetAddress = '';
    if ($(this).attr('data-sandra-ref-active') !== undefined) {
        targetAddress = $(this).attr('data-sandra-ref-active');
    }
    else
        targetAddress = $(this).text();
    // similar behavior as clicking on a link
    window.location.href = "addressIndex.html?address=" + targetAddress;
});
function convertTimestamp() {
    // @ts-ignore
    if (moment !== undefined) {
        console.log("⏱️ converting timestamps");
        $(".timestampToDate").html(function (index, value) {
            // @ts-ignore
            return moment(value * 1000).format("YYYY-MM-DD hh:mm");
        });
    }
    else {
        console.warn("⏱️ momentjs not included");
    }
}
function displayAddress(address) {
    $('.activeAddress').html(address);
}
function removeMintLink() {
    $(".addressLink").text(function (index, value) {
        value = value.replace(/\s/g, '');
        if (value == CSCanonizeManager.mintIssuerAddressString) {
            $(this).text("MINT");
            $(this).contents().unwrap();
            return "mint";
        }
        console.log(value);
        return value;
    });
}
function findGetParameter(parameterName) {
    var result = '', tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
        tmp = item.split("=");
        if (tmp[0] === parameterName)
            result = decodeURIComponent(tmp[1]);
    });
    return result;
}
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
    console.log("lazyload instanciated");
}
