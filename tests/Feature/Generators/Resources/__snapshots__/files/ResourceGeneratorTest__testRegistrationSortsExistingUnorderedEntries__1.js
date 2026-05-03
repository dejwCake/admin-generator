import './bootstrap';
import { createApp } from 'vue';

//-- Do not delete me :) I'm used for auto-generation js import begin --
import AdminUserForm from './admin-user/Form.vue';
import CategoryListing from './category/Listing.vue';
import PostListing from './post/Listing.vue';
//-- Do not delete me :) I'm used for auto-generation js import end --

const app = createApp({});

//-- Do not delete me :) I'm used for auto-generation component registration begin --
app.component('AdminUserForm', AdminUserForm);
app.component('CategoryListing', CategoryListing);
app.component('PostListing', PostListing);
//-- Do not delete me :) I'm used for auto-generation component registration end --

app.mount('#app');