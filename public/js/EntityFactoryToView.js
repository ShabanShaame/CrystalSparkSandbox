"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.EntityFactoryToView = void 0;
var EntityFactoryToView = /** @class */ (function () {
    function EntityFactoryToView() {
    }
    EntityFactoryToView.viewInit = function () {
        var factoryField = $('.sandra-entity-factory');
        factoryField.hide();
    };
    EntityFactoryToView.factoryToView = function (entityFactory, factoryFieldClass) {
        var factoryField = $('.' + factoryFieldClass);
        var entityField = factoryField.find($('.sandra-entity'));
        factoryField.html('');
        var thisClass = this;
        entityFactory.entityArray.forEach(function (entity) {
            var newField = entityField.clone();
            $("[data-ref]", newField).each(function () {
                var ref = $(this).data('ref');
                var value = entity.getRefValue(ref);
                $(this).text(value);
            });
            $("[data-joined-on]", newField).each(function () {
                var ref = $(this).data('joined-ref');
                var verb = $(this).data('joined-on');
                if (ref == undefined || verb == undefined) {
                    $(this).text('');
                }
                else {
                    $(this).text(EntityFactoryToView.joinedEntityRef(entity, verb, ref));
                }
            });
            $("[data-src-ref]", newField).each(function () {
                var ref = $(this).data('src-ref');
                var value = entity.getRefValue(ref);
                $(this).attr('src', value);
            });
            //for the lazy load when the image is in data-src
            $("[data-src-data-src-ref]", newField).each(function () {
                var ref = $(this).data('src-data-src-ref');
                var value = entity.getRefValue(ref);
                $(this).attr('data-src', value);
            });
            $("[data-ref-name]", newField).each(function () {
                var ref = $(this).data('ref-name');
                var value = entity.getRefValue(ref);
                $(this).attr('data-sandra-ref-' + ref, value);
            });
            factoryField.append(newField);
        });
        factoryField.show();
    };
    EntityFactoryToView.joinedEntityRef = function (entity, verb, ref) {
        var joined = entity.getJoinedEntitiesOnVerb(verb);
        var first = joined[0];
        if (!first)
            return '';
        return first.getRefValue(entity.factory.sandraManager.get(ref));
    };
    return EntityFactoryToView;
}());
exports.EntityFactoryToView = EntityFactoryToView;
//# sourceMappingURL=EntityFactoryToView.js.map