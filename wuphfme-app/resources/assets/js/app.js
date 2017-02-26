
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.


Vue.component('example', require('./components/Example.vue'));

const app = new Vue({
    el: '#app'
});
*/

Vue.component('sync-text', {
	template: '<input type="text" :disabled="syncing?true:false" v-model.lazy="value" />',
	data: function(){
		var data_inter = {
			value: "",
			syncing: false,
			flapProtect: ""
		};
		
		if (!this.initial) {
			data_inter.syncing = true;
			axios.get(this.endpoint)
			.then(function (response) {
				if(response.status==200 && typeof response.data === 'string'){
					data_inter.value = response.data;
					data_inter.syncing = false;
					data_inter.flapProtect = response.data;
				}
			});
		} else {
			data_inter.value = this.initial;
		}
		
		return data_inter;
	},
	props: [
		"endpoint",
		"initial"
	],
	watch: {
		value: function (newval, oldval){
			var self = this;
			if (this.syncing) return;
			if (newval == this.flapProtect) {
				this.flapProtect = "";
				return;
			}
			this.flapProtect = oldval;
			this.syncing = true;
			
			axios.post(this.endpoint, {
				val: newval
			})
			.then(function (response) {
				if(response.status==200 && response.data=="set"){
					self.value = newval;
					self.flapProtect = "";
				}else{
					self.value = oldval;
					console.log(response.status);
					console.log(response.data);
				}
				self.syncing = false;
			})
			.catch(function (error) {
				console.log(error);
				self.value = oldval;
				self.syncing = false;
			});
		}
	}
});