<template>
    <div>
        <h3>Customer File</h3>

        <slot name="AssetSelectInput"></slot>

        <div class="btngroup">
            <a v-if="customerFileUid" class="btn small" :href="customerFileUid|assetDownload">Download</a>
            <div class="btn submit small" role="button" @click="submit()">Save</div>
        </div>
        <div v-if="working" class="spinner"></div>
        <div v-if="error" class="error">{{error}}</div>
    </div>
</template>

<script>
    /* global Craft */

    import axios from 'axios';

    export default {
        name: 'customer-file',
        props: ['file','lineItemId','source'],
        data() {
            return {
                customerFileUid: this.file ? this.file.uid : null,
                assetSelectInput: null,
                working: false,
                error: null,
            }
        },
        mounted() {
            Craft.initUiElements();
            this.initAssetSelectInput();
        },
        filters: {
            assetDownload: function(uid) {
                return Craft.getActionUrl('print-shop/files/download', {
                    uid: uid
                });
            }
        },
        methods: {

            initAssetSelectInput() {
                this.$nextTick(function () {
                    this.assetSelectInput = new Craft.AssetSelectInput({
                        elementType: "craft\\elements\\Asset",
                        id: "newFile-"+this.lineItemId+"-asset",
                        limit: 1,
                        modalStorageKey: null,
                        name: "newFile["+this.lineItemId+"][asset]",
                        sources: [this.source],
                    });
                });
            },

            submit () {

                this.working = true;
                this.error = null;

                // Get the data out of the form inputs
                let data = {
                    lineItemId: this.lineItemId,
                    assetIds: this.assetSelectInput.getSelectedElementIds(),
                };

                // Make the POST request
                axios.post(Craft.getActionUrl('print-shop/files/save'), data, {
                        headers: {
                            'X-CSRF-Token': Craft.csrfTokenValue,
                        }
                    })
                    .then(response => {
                        this.working = false;

                        if (response.data.error) {
                            this.error = response.data.error;
                        } else {
                            this.customerFileUid = response.data.file.uid;
                            this.$emit('new-file-added');
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
    .btngroup {
        margin-top: 12px;
    }
    .spinner {
        vertical-align: baseline;
    }
    .error {
        margin-top: 12px;
    }
</style>
