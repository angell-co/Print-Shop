<template>
  <div>
    <table class="data fullwidth" v-if="proofsList.length > 0">
      <thead>
        <tr>
          <th>File</th>
          <th>Status</th>
          <th>Date</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="proof in proofsList" :key="proof.uid">
          <td>
            <a :href="proof.uid|assetDownload">{{proof.asset.filename}}</a>
          </td>
          <td width="90px">
            <span class="status" :class="{
              white: proof.status == 'new',
              green: proof.status == 'approved',
              red: proof.status == 'rejected'
            }"></span>
            {{proof.status|capitalize}}
          </td>
          <td width="220px">{{proof.date|date}}</td>
          <td>
            <div v-if="proof.staffNotes">
              <strong>Staff Notes:</strong><br>
              <nl2br tag="p" :text="proof.staffNotes" />
            </div>
            <div v-if="proof.customerNotes">
              <strong>Customer Notes:</strong><br>
              <nl2br tag="p" :text="proof.customerNotes" />
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="proofsList.length === 0 && !showProofForm" class="error">No proofs yet, <a role="button" @click="onShowProofForm()">add one</a>.</div>

    <div class="btn submit"
         role="button"
         v-if="!showProofForm"
         @click="onShowProofForm()">Add proof</div>

    <div class="pane" v-if="showProofForm">
      <table class="proofform data fullwidth">
        <tbody>
          <tr>
            <td width="200px">
              <slot name="AssetSelectInput"></slot>
            </td>
            <td>
              <slot name="StaffNotesField"></slot>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="btn submit" role="button" @click="submit()">Save</div>
      <div v-if="working" class="spinner"></div>
      <div v-if="error" class="error">{{error}}</div>
    </div>
  </div>
</template>

<script>
  /* global Craft */

  import axios from 'axios';
  import moment from 'moment';
  import nl2br from 'vue-nl2br';

  export default {
    name: 'proofs',
    props: ['proofs','lineItemId','source'],
    components: {
      nl2br,
    },
    data () {
      return {
        assetSelectInput: null,
        working: false,
        error: null,
        showProofForm: false,
        proofsList: this.proofs
      };
    },
    mounted() {
      Craft.initUiElements();
    },
    filters: {
      date: function (date) {
        return moment(date).format('MMMM Do YYYY, h:mm a');
      },
      assetDownload: function(uid) {
        return Craft.getActionUrl('print-shop/proofs/download', {
          uid: uid
        });
      },
      capitalize: function (value) {
        if (!value) return '';
        value = value.toString();
        return value.charAt(0).toUpperCase() + value.slice(1);
      }
    },
    methods: {
      onShowProofForm () {
        this.showProofForm = true;
        this.$nextTick(function () {
          this.assetSelectInput = new Craft.AssetSelectInput({
            elementType: "craft\\elements\\Asset",
            id: "newProof-"+this.lineItemId+"-asset",
            limit: 1,
            modalStorageKey: null,
            name: "newProof["+this.lineItemId+"][asset]",
            sources: [this.source],
          });
        });
      },
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
            // Add proof to the stack
            this.proofsList.push(response.data.proof);
            // Close the form
            this.showProofForm = false;
          }
        })
        .catch(response => {
          this.working = false;
          this.error = response;
        });

      }
    }
  }
</script>

<style scoped type="text/scss">
  table.data tbody tr td,
  table.data tbody tr th {
    border-top: 0;
    vertical-align: top;
  }

  table.proofform.data tbody tr td {
    vertical-align: top;
    border-bottom: 0;
    border-top: 0;
  }

  .btn {
    margin-top: 24px;
  }
  .spinner {
    vertical-align: baseline;
  }

  .pane {
    margin-top: 24px;
    margin-bottom: 24px !important;
  }

  @media only screen and (max-width: 1600px) {
    table.proofform.data tbody tr td {
      display: block;
      padding-left: 0 !important;
      margin-bottom: 14px;
    }
  }
</style>
