<?php


namespace App\Guillermo;


use App\shaban\SqlResponses;
use Illuminate\Support\Facades\DB;
use SandraCore\Entity;
use SandraCore\EntityFactory;
use SandraCore\System;

class MainExercises
{

    public function exercise1():array{

        //complete the SQL statement please keep column names
        return DB::select( "SELECT t.id AS entity_id,
                                COALESCE(cs.shortname, cs.code) AS Subject,
                                COALESCE(cv.shortname, cv.code) AS Verb,
                                COALESCE(ct.shortname, ct.code) AS Target
                            FROM testgame_SandraTriplets t
                                INNER JOIN testgame_SandraConcept AS cs ON t.idConceptStart = cs.id
                                INNER JOIN testgame_SandraConcept AS cv ON t.idConceptLink = cv.id
                                INNER JOIN testgame_SandraConcept AS ct ON t.idConceptTarget = ct.id
                        ");


    }

    public function exercise2():array{

        //complete the SQL statement please keep column names
        return DB::select( "SELECT t.id AS entity_id,
                                COALESCE(cs.shortname, cs.code) AS Subject,
                                COALESCE(cv.shortname, cv.code) AS Verb,
                                COALESCE(ct.shortname, ct.code) AS Target
                            FROM testgame_SandraTriplets t
                                RIGHT JOIN testgame_SandraConcept AS cs ON t.idConceptStart = cs.id
                                    AND t.idConceptStart NOT IN (
                                        SELECT t2.idConceptStart FROM testgame_SandraTriplets t2
                                            INNER JOIN testgame_SandraConcept AS c2 ON t2.idConceptTarget = c2.id
                                            WHERE c2.shortname = 'annoyingInsectFile'
                                    )
                                INNER JOIN testgame_SandraConcept AS cv ON t.idConceptLink = cv.id
                                INNER JOIN testgame_SandraConcept AS ct ON t.idConceptTarget = ct.id
                        ");


    }

    public static function areEntitiesEmpty(null|array $entities): bool {
        return $entities === null || count($entities) <= 0;
    }

    public static function extractLocalizedName(Entity|null $entity, string|null $favoriteLanguage): string {
        $localizedEntity = $favoriteLanguage !== null
            ? $entity->getBrotherEntity('localizedFile', $favoriteLanguage)
            : null;
        return (
            $localizedEntity !== null
                ? $localizedEntity
                : $entity
        )->get('name');
    }

    public static function loadCountryFactory(System $sandra): EntityFactory {
        $entityFactory = new EntityFactory('country','generalCountryFile',$sandra);
        $entityFactory->populateLocal();
        $entityFactory->getTriplets();
        $entityFactory->populateBrotherEntities('localizedFile');
        return $entityFactory;
    }

    public static function loadCityFactory(System $sandra, EntityFactory $countryFactory): EntityFactory {
        $entityFactory = self::loadCountryFactory($sandra);
        $entityFactory = new EntityFactory('city','generalCityFile',$sandra);
        $entityFactory->joinFactory('locatedIn', $countryFactory);
        $entityFactory->populateLocal();
        $entityFactory->getTriplets();
        $entityFactory->joinPopulate();
        $entityFactory->populateBrotherEntities('localizedFile');
        return $entityFactory;
    }

    public function exercise3(string $favoriteLanguage = null):array{


        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));

        $countryFactory = self::loadCountryFactory($sandra);
        $cityFactory = self::loadCityFactory($sandra, $countryFactory);

        //create an array with all cities names with their respective countries
        // $response[] = "City Name".' - '."Country name";
        $cityCountryStringOrNullArray = array_map(
            function (Entity $city) use ($favoriteLanguage) {
                $countries = $city->getJoinedEntities('locatedIn');
                if (self::areEntitiesEmpty($countries)) return null;
                $country = current($countries);
                return MainExercises::extractLocalizedName($city, $favoriteLanguage)
                    . ' - '
                    . MainExercises::extractLocalizedName($country, $favoriteLanguage);
            },
            $cityFactory->getEntities()
        );

        $response = array_filter($cityCountryStringOrNullArray, function (string|null $el) { return is_string($el); });

        return $response ;

    }

    public static function joinGetMaybeEmptyEntities(Entity $subjectEntity, string $verb, EntityFactory $joinedFactory): array|null {
        $subjectEntity->factory->joinFactory($verb, $joinedFactory);
        $subjectEntity->factory->joinPopulate();
        $joinedEntities = $subjectEntity->getJoinedEntities($verb);
        return $joinedEntities;
    }

    public static function inferLanguage(System $sandra, Entity $entity): string {
        $entity->factory->populateBrotherEntities('speak');
        $speak = $entity->getBrotherEntity('speak');
        if (!self::areEntitiesEmpty($speak)) {
            $lang = current($speak)->targetConcept->getShortName();
            return $lang;
        }

        $entityIsa = $entity->factory->entityIsa;
        $countryFactory = self::loadCountryFactory($sandra);
        $supportedJoinsConfig = [
            'person' => [
                'subject' => 'person',
                'verb' => 'bornInCity',
                'factory' => self::loadCityFactory($sandra, $countryFactory),
            ],
            'city' => [
                'subject' => 'city',
                'verb' => 'locatedIn',
                'factory' => $countryFactory,
            ],
        ];

        if (!isset($supportedJoinsConfig[$entityIsa])) {
            throw new \Exception('Subject has no direct "speak" verb and is unsupported for inference.');
        }

        $joinConfig = $supportedJoinsConfig[$entityIsa];

        $joinedEntities = self::joinGetMaybeEmptyEntities($entity, $joinConfig['verb'], $joinConfig['factory']);

        if (!self::areEntitiesEmpty($joinedEntities)) {
            return self::inferLanguage($sandra, current($joinedEntities));
        }

        throw new \Exception('Unable to infer language from current graph data, replace this with "return \'english\';" to use english as default value');
    }

    public function exercise4(Entity $entityToFindLanguage = null):array{

        //initialise datagraph to recreate tables
        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));

        //create an array with all cities names with their respective countries
        // $response[] = "City Name".' - '."Country name";
        $response = $this->exercise3(self::inferLanguage($sandra, $entityToFindLanguage));

        return $response ;

    }

}
