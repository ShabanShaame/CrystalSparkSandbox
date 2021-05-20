
import {EntityFactory} from "canonizer/src/EntityFactory.js";
import {Entity} from "canonizer/src/Entity.js";
import * as $ from "jquery";




export class EntityFactoryToView{


    public static viewInit(){

        let factoryField = $('.sandra-entity-factory');
        factoryField.hide();

    }


    public static factoryToView(entityFactory:EntityFactory,factoryFieldClass:string){


        let factoryField = $('.'+factoryFieldClass);
        let entityField = factoryField.find($('.sandra-entity'));
        factoryField.html('');

        const thisClass = this ;


        entityFactory.entityArray.forEach(entity =>{

            let newField = entityField.clone();



            $("[data-ref]", newField).each(function(){
                const ref = $(this).data('ref');
                const value = entity.getRefValue(ref);
                $(this).text(value);
            });

            $("[data-joined-on]", newField).each(function(){

                const ref = $(this).data('joined-ref');
                const verb = $(this).data('joined-on');

                if (ref == undefined || verb == undefined){
                    $(this).text('');
                }
                else {

                    $(this).text(EntityFactoryToView.joinedEntityRef(entity, <string>verb, <string>ref));
                }
            });

            $("[data-src-ref]", newField).each(function(){
                const ref = $(this).data('src-ref');
                const value = entity.getRefValue(ref);

                $(this).attr('src',value);
            });

            //for the lazy load when the image is in data-src
            $("[data-src-data-src-ref]", newField).each(function(){
                const ref = $(this).data('src-data-src-ref');
                const value = entity.getRefValue(ref);
                console.log("helloThisTim entity")
                console.log(entity);

                $(this).attr('data-src',value);
            });

            $("[data-ref-name]", newField).each(function(){
                const ref = $(this).data('ref-name');
                const value = entity.getRefValue(ref);


                $(this).attr('data-sandra-ref-'+ref,value);
            });

            factoryField.append(newField);

            }


        )

        factoryField.show();


    }


    public static joinedEntityRef(entity:Entity,verb:string,ref:string){



    const joined = entity.getJoinedEntitiesOnVerb(verb);

       const first = joined[0];

       if (!first) return ''



        return first.getRefValue(entity.factory.sandraManager.get(ref));


    }

}
