
window.Vue = require('vue');
window.BootstrapVue = require('bootstrap-vue');

Vue.use(BootstrapVue);

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap-vue/dist/bootstrap-vue.css');

Vue.component('test-component', require('./components/TestComponent').default);
Vue.component('cart-component', require('./components/Cart/CartComponent').default);
Vue.component('order-register-component', require('./components/Order/OrderRegisterComponent').default);
Vue.component('dash-component', require('./components/DashComponent').default);

var app = new Vue({
  el: '#sub-container'
});