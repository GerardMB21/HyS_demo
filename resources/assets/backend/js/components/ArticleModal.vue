<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url_edit, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Editar {{ this.model.name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Stock de Artículo</label>
                                        <input type="text" class="form-control" name="stock" id="stock" placeholder="0" v-model="model.stock" @focus="$parent.clearErrorMsg($event)">
                                        <div id="stock-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Fecha de Expiracion</label>
                                        <datetime
                                            v-model="expiration_date_parse"
                                            placeholder="Selecciona una Fecha"
                                            :format="'dd-LL-yyyy'"
                                            input-id="expiration_date"
                                            name="expiration_date"
                                            value-zone="America/Lima"
                                            zone="America/Lima"
                                            class="form-control"
                                            :min-datetime="current_date"
                                            @focus="$parent.clearErrorMsg($event)">
                                        </datetime>
                                        <div id="expiration_date-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="add_article_2">
                            {{ button_text }}
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
        </div>
    </div>
    <!--end::Modal-->
</template>

<script>
    import EventBus from '../event-bus';
    import Datetime from 'vue-datetime';
    import 'vue-datetime/dist/vue-datetime.css';

    Vue.use(Datetime);

    export default {
        props: {
            url_edit: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    id: 0,
                    stock: 0,
                    expiration_date: '',
                    name: ''
                },
                button_text: '',
                current_date: '',
                expiration_date_parse: '',
            }
        },
        watch: {
            'model.expiration_date': function(val) {
                const array = val.split('/');
                const date = `${array[2]}-${array[1]}-${array[0]}`;

                this.current_date = date;
                this.expiration_date_parse = date;
            }
        },
        computed: {
            
        },
        created() {
            EventBus.$on('edit_modal', function(data) {
                this.model = data;
                this.button_text = 'Actualizar';
                $('#modal').modal('show');
            }.bind(this));
        },
        mounted() {
            
        },
        methods: {
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);

                const id = vm.model.id;
                const stock = vm.model.stock;
                const expiration_date = vm.expiration_date_parse;
                const warehouse_type_id = vm.$store.state.warehouse_type_id;

                EventBus.$emit('loading', true);

                // EventBus.$emit('loading', true);
                axios.post(url, {
                    warehouse_type_id,
                    id,
                    stock,
                    expiration_date
                }).then(response => {
                    EventBus.$emit('loading', false);

                    this.model = {
                        id: 0,
                        stock: 0,
                        expiration_date: '',
                        name: ''
                    };
                    this.expiration_date_parse = '';

                    $('#modal').modal('hide');

                    Swal.fire({
                        title: '¡Ok!',
                        text: 'Se ha actualizado correctamente',
                        type: "success",
                        heightAuto: false,
                    });

                    const data = response.data;

                    EventBus.$emit('refresh_table', data);
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('.modal').animate({
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