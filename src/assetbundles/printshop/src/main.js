/* global Craft */
/* global Garnish */

import Vue from 'vue';
import Proofs from './components/Proofs.vue';
import LineItem from './components/LineItem.vue';
import CustomerFile from './components/CustomerFile.vue';

import './main.scss';

Garnish.$doc.ready(function() {
  Craft.initUiElements();

  window.printShop = new Vue({
    el: "#printshop",
    delimiters: ['${', '}'],
    components: {
      Proofs,
      LineItem,
      CustomerFile,
    }
  });

});
