import AppForm from '../app-components/Form/AppForm';

Vue.component('profile-edit-password-form', {
    mixins: [AppForm],
    data: function() {
        return {
            form: {
                title:  '' ,
                
            }
        }
    }

});