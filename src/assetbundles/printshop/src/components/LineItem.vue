<template>

    <div class="matrixblock">
        <slot name="Title"></slot>

        <div class="actions">
            <span class="status"
                 :class="{
                   red: proofStatus === 'no proof',
                   green: proofStatus === 'approved',
                   orange: proofStatus === 'rejected'
                 }">
            </span>
            {{ proofStatus === 'new' ? 'Pending' : proofStatus|capitalize }}
            <br>
            <a :data-icon="showContent ? 'collapse' : 'expand'"
               class="expand-collapse"
               @click.prevent="toggleShowContent()">{{ showContent ? 'Collapse' : 'Expand' }}</a>
        </div>

        <template v-if="showContent">
            <slot name="Meta"></slot>
            <hr>
            <slot name="CustomerFile"></slot>
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
                showContent: this.proofStatus === 'no proof' || this.proofStatus === 'rejected',
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
            }
        }
    }
</script>

<style scoped type="text/scss">
    body.ltr #printshop .matrixblock > .titlebar {
        padding: 5px 200px 5px 15px;
    }

    .actions {
        padding-right: 10px;
    }
    .actions > .expand-collapse {
        margin-top: 3px;
        margin-right: 0;
    }
    .actions > .expand-collapse:before {
        margin-right: 12px;
    }
</style>