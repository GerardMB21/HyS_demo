<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Buscar
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Botica:</label>
                            <select class="form-control" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="0" selected>Seleccionar</option>
                                <option v-for="warehouse_type in warehouse_types" :value="warehouse_type.id" v-bind:key="warehouse_type.id">{{ warehouse_type.name }}</option>
                            </select>
                            <div id="warehouse_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-lg-6">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--end::Form-->
    </div>
    <!--end::Portlet-->
</template>

<script>
    import EventBus from '../event-bus';
    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                warehouse_types: [
                    {
                        id : 1,
                        name : 'PACHACAMAC'
                    },
                    {
                        id : 2,
                        name : 'VITARTE 5 JESUS DE NAZARETH'
                    },
                    {
                        id : 3,
                        name : 'VITARTE 1'
                    },
                ],
                model: {
                    warehouse_type_id: 0,
                },
            }
        },
        created() {

        },
        mounted() {

        },
        watch: {
            'model.warehouse_type_id': function(val) {
                this.$store.state.warehouse_type_id = val;
            }
        },
        computed: {

        },
        methods: {
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;

                const warehouse_type_id = vm.model.warehouse_type_id;
                EventBus.$emit('loading', true);

                axios.post(url, { warehouse_type_id })
                .then(response => {
                    EventBus.$emit('show_table', {
                        'data': response.data,
                    });
                })
                .catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
                        let c_target = target.find("#" + i + "-error");
                        if (!c_target.attr('data-required')) {
                            let p = c_target.prev();
                            p.addClass('is-invalid');
                        } else {
                            c_target.css('display', 'block');
                        }
                        c_target.html(item);
                    });
                });
            },
        }
    };
</script>