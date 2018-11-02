window.Vue = require('vue')
window.VueDaumPostcode = require('vue-daum-postcode');

Vue.use(VueDaumPostcode);


Vue.component('test-component', require('./components/TestComponent').default);
Vue.component('product-detail-component', require('./components/Product/ProductDetailComponent').default);

var app = new Vue({
  el: '#sub-container'
});
