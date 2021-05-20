import {CSCanonizeManager} from "canonizer/src/canonizer/CSCanonizeManager";

// Oedo
let jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbnYiOiJvZWRvIiwiZmx1c2giOnRydWUsImV4cCI6MTA0ODA3MjU1MTYyNDAwMH0.Plod9xQFnkonkUAZk88n63ykpj56u6vWS-5pwiWznXw';

// WestEnd
// let jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbnYiOiJzaGlidXlhIiwiZmx1c2giOmZhbHNlLCJleHAiOjEwNDc0NDY1NzcxNDQwMDB9.VNMArL_m04pSxuOqaNbwGc38z-bfQnHntGJHa2FgAXQ';

//let canonizeManager = new CSCanonizeManager({connector:{gossipUrl:'http://arkam.everdreamsoft.com/alex/gossip',jwt:jwt}});
let canonizeManager = new CSCanonizeManager({connector:{gossipUrl:'http://arkam.everdreamsoft.com/alex/gossip',jwt:jwt}});

let sandra = canonizeManager.getSandra();


