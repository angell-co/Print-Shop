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
    props: ['proofs'],
    mounted() {
      this.$nextTick(function () {
        new Craft.AssetSelectInput({
          criteria: {kind: ['image']},
          elementType: "craft\\elements\\Asset",
          fieldId: null,
          id: "newProof-2-asset",
          limit: 1,
          modalStorageKey: null,
          name: "newProof[2][asset]",
          showSiteMenu: false,
          sortable: true,
          sourceElementId: null,
          sources: ["volume:760c9b48-e1b8-4caa-bd14-1136c7b59160"],
          viewMode: "list"
        });
      })
    }
  }
</script>

<style scoped>

</style>
