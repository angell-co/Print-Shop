<template>
  <div>
    <h1>Proofs</h1>
    <pre>{{ proofs }}</pre>

    <table class="data fullwidth collapsible">
      <tbody>
        <tr class="order-assets-fieldtype__line-item__form">
          <td></td>
          <td width="200px">
              <slot name="AssetSelectInput"></slot>
          </td>

          <td>
            <slot name="StaffNotesField"></slot>
          </td>

          <td>
            <div class="btn submit" role="button">Add</div>
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
    mounted() {
      this.$nextTick(function () {
        new Craft.AssetSelectInput({
          elementType: "craft\\elements\\Asset",
          id: "newProof-"+this.lineItemId+"-asset",
          limit: 1,
          modalStorageKey: null,
          name: "newProof["+this.lineItemId+"][asset]",
          sources: [this.source],
        });
      })
    }
  }
</script>

<style scoped>

</style>
