<template>

    <div class="matrixblock"
         :class="{
            'printshop-lineitem-collapsed': !showContent
         }">
        <slot name="Title"></slot>

        <div class="actions">
            <span class="status"
                 :class="{
                   red: status === 'no proof',
                   green: status === 'approved',
                   orange: status === 'rejected'
                 }">
            </span>
            {{ status === 'new' ? 'Pending' : status|capitalize }}
            <a :data-icon="showContent ? 'collapse' : 'expand'"
               class="expand-collapse"
               @click.prevent="toggleShowContent()">{{ showContent ? 'Collapse' : 'Expand' }}</a>
        </div>

        <template v-if="showContent">
            <slot name="Meta"></slot>
            <hr>
            <slot name="CustomerFile"
                  :changeStatus="changeStatus"
                  :onNewCustomerFileAdded="onNewCustomerFileAdded"></slot>
        </template>
    </div>

</template>

<script>
    /* global Craft */
    /* global $ */

    export default {
        name: 'line-item',
        props: ['lineItem', 'proofStatus'],
        data() {
            return {
                status: this.proofStatus,
                showContent: true
            };
        },
        mounted() {
            Craft.initUiElements();
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1);
            }
        },
        methods: {
            toggleShowContent () {
                this.showContent = !this.showContent;
                if (this.showContent) {
                    this.$nextTick(function () {
                        new Craft.ElementThumbLoader().load($('.printshop__element-link'));
                    });
                }
            },
            changeStatus (status) {
                this.status = status;
            },
            onNewCustomerFileAdded () {
                this.$children.forEach(el => {
                    if (typeof el.hasCustomerFile !== "undefined") {
                        el.$vnode.componentInstance.showAll = true;
                    }
                });
            }
        }
    }
</script>

<style scoped type="text/scss">
    body.ltr #printshop .matrixblock > .titlebar {
        padding: 5px 200px 5px 15px;
    }
    body.rtl #printshop .matrixblock > .titlebar {
        padding: 5px 15px 5px 200px;
    }

    .actions {
        padding-right: 10px;
        padding-top: 12px;
    }
    .actions > .expand-collapse {
        margin-top: 3px;
        margin-right: 0;
        margin-left: 12px;
    }
    .actions > .expand-collapse:before {
        margin-right: 6px;
    }

    .matrixblock.printshop-lineitem-collapsed {
        padding-bottom: 0;
    }
    .matrixblock.printshop-lineitem-collapsed > .titlebar {
        margin-bottom: 0;
        border-bottom: 0;
        border-radius: 4px;
    }
</style>