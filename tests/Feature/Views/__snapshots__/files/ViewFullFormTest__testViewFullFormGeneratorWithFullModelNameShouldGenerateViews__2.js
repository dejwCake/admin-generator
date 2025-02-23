import AppForm from '../app-components/Form/AppForm';

Vue.component('categ-ory-form', {
    mixins: [AppForm],
    data: function() {
        return {
            form: {
                user_id:  '' ,
                title:  '' ,
                slug:  '' ,
                perex:  '' ,
                published_at:  '' ,
                date_start:  '' ,
                time_start:  '' ,
                date_time_end:  '' ,
                text:  this.getLocalizedFormDefaults() ,
                description:  this.getLocalizedFormDefaults() ,
                enabled:  false ,
                send:  false ,
                price:  '' ,
                views:  '' ,
                created_by_admin_user_id:  '' ,
                updated_by_admin_user_id:  '' ,
            }
        }
    }
});