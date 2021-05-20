// import {CsCannonApiManager} from "./sandra/src/CSCannon/CsCannonApiManager.js";
// import {CSCanonizeManager} from "./sandra/src/CSCannon/CSCanonizeManager.js";
// import {EntityFactoryToView} from "./EntityFactoryToView.js";
 import {Reference} from "canonizer/src/Reference.js";
 import {BlockchainEventFactory} from "canonizer/src/canonizer/BlockchainEventFactory.js";


import {CSCanonizeManager} from "canonizer/src/canonizer/CSCanonizeManager";
import {CsCannonApiManager} from "canonizer/src/canonizer/CsCannonApiManager";
import {EntityFactoryToView} from "./EntityFactoryToView.js";

export class App {
    private apiManager: CsCannonApiManager;

    public constructor() {
        let canonizeManager = new CSCanonizeManager();
        let apiUrl = 'https://ksmjetski.everdreamsoft.com/';

        try{
            // @ts-ignore
            const cookieUrl = getCookie('apiUrl') ;
            if (cookieUrl){
                apiUrl = cookieUrl;
            }

        }
        catch (e) {

        }


        this.apiManager = new CsCannonApiManager(canonizeManager, apiUrl) ;
        console.log("app loaded --");



        EntityFactoryToView.viewInit();

    }

    public async displayCollections(){

    let collections = await this.apiManager.getCollections();

    if (collections.length>0) {
        let factory = collections[0].factory
        EntityFactoryToView.factoryToView(factory,'collectionFactory');


    }



    }

    public async displayAssets(collectionId:string){

        let assetsFactory = await this.apiManager.getCollectionAssets(collectionId);

            EntityFactoryToView.factoryToView(assetsFactory,'assetFactory');

    }

    public async displayCollectionEvents(collectionId:string){

        let eventFactory = await this.apiManager.getCollectionEvents(collectionId);

        EntityFactoryToView.factoryToView(eventFactory,'eventFactory');
        return eventFactory ;

    }


    public async displayAddressEvents(address:string){

        let assetsFactory = await this.apiManager.getAddressEvents(address);

        EntityFactoryToView.factoryToView(assetsFactory,'eventFactory');

    }

    public async displayBalance(address:string){

        let assetsFactory = await this.apiManager.getAddressBalance(address);

        EntityFactoryToView.factoryToView(assetsFactory,'assetFactory');

    }

    public async displayLastActiveAccounts(){

        console.log(this.apiManager);
        let eventFactory = await this.apiManager.getEvents();
        eventFactory.entityArray = eventFactory.entityArray.slice(0,10);

        eventFactory.entityArray.forEach(entity =>{
            let sourceAddress = entity.getJoinedEntitiesOnVerb('source')[0];
            let sourceAddressString = sourceAddress.getRefValue('address');
            let activeAddressString = sourceAddressString;

            if (sourceAddressString == '0x0000000000000000000000000000000000000000' ||
                sourceAddressString == '0x0'
            ){

                let destAddress = entity.getJoinedEntitiesOnVerb('hasSingleDestination')[0];
                activeAddressString = destAddress.getRefValue('address') ;
            }


            entity.addReference(new Reference(entity.factory.sandraManager.get('active'),activeAddressString))

        })


        EntityFactoryToView.factoryToView(eventFactory,'recentAddressFactory');

        return eventFactory ;

    }

    public async displayLatestAssets(eventFactory:BlockchainEventFactory){

        EntityFactoryToView.factoryToView(eventFactory,'recentAssetFactory');
    }

}



const app = new App();
// @ts-ignore
app.displayBalance('DMs4NstKrNkutq6A2wLLMTeQLZZUgbpEYbZeG2cmW1HampZ').then(  lazyload());








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
    console.log("The real lazy load");


}





