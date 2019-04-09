<template>
  <div>
    <h1>Proofs</h1>
    <pre>{{ proofs }}</pre>

    <table class="data fullwidth collapsible">
      <tbody>
        <tr>
          <td></td>
          <td width="200px">
            <slot name="AssetSelectInput"></slot>
          </td>
          <td>
            <slot name="StaffNotesField"></slot>
          </td>
          <td>
            <div class="btn submit" role="button" @click="submit()">Add</div>
            <div class="spinner hidden"></div>
            <div class="error hidden"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
  export default {
    name: 'proofs',
    props: ['proofs','lineItemId','source'],
    data () {
      return {
        assetSelectInput: null,
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
      })
    },
    methods: {
      submit () {

        // Get the data out of the form inputs
        let notesField = document.getElementById('newProof-'+this.lineItemId+'-notes');
        let data = {
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
          return console.log(response.data)
        })
        .catch(response => {
          return console.log(response)
        });

      }
    }
  }
</script>

<style scoped type="text/scss">
  td {
    vertical-align: top;
  }

  td .btn {
    margin-top: 24px;
  }
</style>
