import './bootstrap';
import { createApp } from 'vue';

import LoginForm from '@craftable/auth/LoginForm.vue';
//-- Do not delete me :) I'm used for auto-generation js import begin --
import CategoryForm from './category/Form.vue';
import CategoryListing from './category/Listing.vue';
import PostForm from './post/Form.vue';
import PostListing from './post/Listing.vue';
//-- Do not delete me :) I'm used for auto-generation js import end --

const app = createApp({});

app.component('LoginForm', LoginForm);

//-- Do not delete me :) I'm used for auto-generation component registration begin --
app.component('CategoryForm', CategoryForm);
app.component('CategoryListing', CategoryListing);
app.component('PostForm', PostForm);
app.component('PostListing', PostListing);
//-- Do not delete me :) I'm used for auto-generation component registration end --

app.mount('#app');
