import AppForm from '../app-components/Form/AppForm';

Vue.component('billing-categ-ory-form', {
    mixins: [AppForm],
    data: function() {
        return {
            form: {
                title:  '' ,
            }
        }
    }
});