
For this exercices you will need to use some homebrew methods to go through 
the datagraph.

####Entity

An entity is defined by the relation of a triple and its associated references.
For example

Antoine - contained_in_file - peopleFile (triplet)

firstName : Antoine (Reference)

![](LocalizationGame/navigation_entity1.png)

In the PHP object Entity we can access the data

Assuming $antoine is an object of type Entity

`$antoine->get('name')`;

Will return the reference value : antoine

To modify or write a new reference on this entity
`$antoine->createOrUpdateRef('name','Antonio');` 

will modify the name

`$antoine->createOrUpdateRef('lastName','Dupont');`
Will add a new reference lastName

It's possible to access triplet data this way

`$antoine->subjectConcept->idConcept ` Will return the concept id of Antoine

`$antoine->verbConcept->idConcept ` Will return the concept id of the verb "contained_in_file"

`$antoine->targetConcept->idConcept ` Will return the concept id of the target "people file"

`$antoine->entityId ` Will return the entity id

#### Brother Entity

Two entities are considered "brother" as long as they share the same subject for example

Antoine - contained_in_file - peopleFile (Entity)

Antoine - bornInCity - peopleFile (Entity)

The two entities a considered as brother as they share the same subject "Antoine".
Note even data refer to the same person they are two distict entities.

We can create new brother entities from any object of type Entity

set brother entity expect a verb a target and an array of references key values

`$antoine->setBrotherEntity('playInstrument','piano',['since'=>1940]);`

Note in general method in sandra core expecting concepts accept parameters that are either
string (it get or create a concept id with the shortname string) a int (concept id) or an object of type Entity (then 
it will take the concept subject concept id) For example

`$antoine->setBrotherEntity('loves',$antoine,[]);`

Antoine - loves - Antoine

`$antoine->setBrotherEntity($antoine->subjectConcept->idConcept,$antoine,[]);

Antoine - Antoine - Antoine`



#### EntityFactory

The entity factories are used to create AND load entities from the datagraph

`$peopleFactory = new EntityFactory('person','peopleFile',$sandra);`

This factory will create or read people entities everytime with two triplets

XXX - is_a - person
XXX - contained_in_file - peopleFile

#### Creation


#### Read
In order to load entities we need to use populateLocal method

$peopleFactory->populateLocal()

This will load into memory all entities that are contained_in_file - peopleFile

