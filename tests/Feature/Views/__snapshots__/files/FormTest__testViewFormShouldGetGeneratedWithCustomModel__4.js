import AppForm from '../app-components/Form/AppForm';

Vue.component('billing-my-article-form', {
    mixins: [AppForm],
    data: function() {
        return {
            form: {
                title:  '' ,
                
            }
        }
    }

});