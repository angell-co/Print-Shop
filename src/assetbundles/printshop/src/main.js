/* global Craft */
/* global Garnish */
/* global $ */

import Vue from 'vue'
import Proofs from './components/Proofs.vue'

Garnish.$doc.ready(function() {
  Craft.initUiElements()

  window.printShop = new Vue({
    el: "#printshop",
    delimiters: ['${', '}'],
    components: {
      Proofs,
    }
  });

});