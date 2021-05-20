import {CSCanonizeManager} from "canonizer/src/canonizer/CSCanonizeManager";



//let canonizeManager = new CSCanonizeManager({connector:{gossipUrl:'http://arkam.everdreamsoft.com/alex/gossip',jwt:jwt}});
let canonizeManager = new CSCanonizeManager({connector:{gossipUrl:'http://arkam.everdreamsoft.com/alex/gossip',jwt:jwt}});

let sandra = canonizeManager.getSandra();


