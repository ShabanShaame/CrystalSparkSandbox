import { CSCanonizeManager } from "canonizer/src/canonizer/CSCanonizeManager";
//let canonizeManager = new CSCanonizeManager({connector:{gossipUrl:'http://arkam.everdreamsoft.com/alex/gossip',jwt:jwt}});
var canonizeManager = new CSCanonizeManager({ connector: { gossipUrl: 'http://arkam.everdreamsoft.com/alex/gossip', jwt: jwt } });
var sandra = canonizeManager.getSandra();
