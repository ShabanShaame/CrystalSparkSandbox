
import {App} from "./app.js";
import {CSCanonizeManager} from "canonizer/src/canonizer/CSCanonizeManager";

console.log("hello console");

var app = new App();
// @ts-ignore



// @ts-ignore
if (page === 'assetList') {
    let collectionId = findGetParameter('collectionId')

    app.displayAssets(collectionId).then(r => {console.log("asset loaded") ;lazyload()});
    app.displayCollectionEvents(collectionId).then(r => {
        console.log("transactions loaded")
            convertTimestamp();
            removeMintLink();
            lazyload();


    }

    );
}

// @ts-ignore
if (page === 'addressBalance') {
    let address = findGetParameter('address')
    displayAddress(address);

   app.displayBalance(address).then(r => {console.log("balance loaded")});
    app.displayAddressEvents(address).then(r => {
            console.log("transactions loaded")
            convertTimestamp();
        removeMintLink();
            lazyload()

        }

    );
}






$(document).on('click', '.collectionLink', function (e) {
    e.preventDefault();
    // similar behavior as clicking on a link
    window.location.href = "galleryAssetList.html?collectionId="+$(this).attr('data-sandra-ref-collectionid');


});

$(document).on('click', '.addressLink', function (e) {
    e.preventDefault();

    let targetAddress:any = '';

    if ($(this).attr('data-sandra-ref-active') !== undefined) {
        targetAddress = $(this).attr('data-sandra-ref-active');
    }
    else
        targetAddress = $(this).text();

    // similar behavior as clicking on a link
    window.location.href = "addressIndex.html?address="+targetAddress;


});


function convertTimestamp(){

    // @ts-ignore
    if ( moment !== undefined ) {
        console.log("⏱️ converting timestamps")
        $(".timestampToDate").html(function (index, value) {

            // @ts-ignore
            return moment(value*1000).format("YYYY-MM-DD hh:mm");
        });
    }
    else {

        console.warn("⏱️ momentjs not included")
    }
}

function displayAddress(address:string){


    $('.activeAddress').html(address)

}

function removeMintLink(){


    $(".addressLink").text(function (index, value) {

        value = value.replace(/\s/g, '');

       if (value == CSCanonizeManager.mintIssuerAddressString) {
           $(this).text("MINT");
           $(this).contents().unwrap();

           return "mint";
       }
       console.log(value);
       return value ;
    });


}



function findGetParameter(parameterName:string) {
    var result = '',
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}

function lazyload(){


    //we should remove from here
    // @ts-ignore
    $('.lazy').Lazy({

        visibleOnly: true,
        // @ts-ignore
        onError: function(element:any) {
            var imageSrc = element.data('src');
            console.log('image "' + imageSrc + '" could not be loaded');
        }


    }); //reload lazy
    console.log("lazyload instanciated");


}

