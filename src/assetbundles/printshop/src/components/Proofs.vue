<template>
  <div>
    <!--  This table (form) needs a background, some padding and a toggle to open it  -->
    <table class="data fullwidth">
      <tbody>
        <tr>
          <td width="200px">
            <slot name="AssetSelectInput"></slot>
          </td>
          <td>
            <slot name="StaffNotesField"></slot>
          </td>
          <td width="100px">
            <div class="btn submit" role="button" @click="submit()">Add</div>
            <div v-if="working" class="spinner"></div>
          </td>
        </tr>
        <tr v-if="error">
          <td colspan="3">
            <div class="error">{{error}}</div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>

  import axios from 'axios';

  export default {
    name: 'proofs',
    props: ['proofs','lineItemId','source'],
    data () {
      return {
        assetSelectInput: null,
        working: false,
        error: null
      };
    },
    mounted() {
      this.$nextTick(function () {
        this.assetSelectInput = new Craft.AssetSelectInput({
          elementType: "craft\\elements\\Asset",
          id: "newProof-"+this.lineItemId+"-asset",
          limit: 1,
          modalStorageKey: null,
          name: "newProof["+this.lineItemId+"][asset]",
          sources: [this.source],
        });

        Craft.initUiElements();
      })
    },
    methods: {
      submit () {

        this.working = true;
        this.error = null;

        // Get the data out of the form inputs
        let notesField = document.getElementById('newProof-'+this.lineItemId+'-notes');
        let data = {
          lineItemId: this.lineItemId,
          assetIds: this.assetSelectInput.getSelectedElementIds(),
          staffNotes: notesField.value
        };

        // Make the POST request
        axios.post(Craft.getActionUrl('print-shop/proofs/save'), data, {
          headers: {
            'X-CSRF-Token': Craft.csrfTokenValue,
          }
        })
        .then(response => {
          this.working = false;

          if (response.data.error) {
            this.error = response.data.error;
          } else {
            // success
          }

          return console.log(response.data);
        })
        .catch(response => {
          this.working = false;
          this.error = response;
          return console.log(response);
        });

      }
    }
  }
</script>

<style scoped type="text/scss">
  table.data tbody tr td {
    vertical-align: top;
    border-bottom: 0;
    border-top: 0;
  }

  table.data tbody td .btn {
    margin-top: 24px;
  }

  @media only screen and (max-width: 1600px) {
    table.data tbody tr td {
      display: block;
      padding-left: 0 !important;
      margin-bottom: 14px;
    }
    table.data tbody td .btn {
      margin-top: 0;
    }
  }
</style>
