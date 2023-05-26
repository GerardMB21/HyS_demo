<template>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-show="show_results">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Resultado
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
						<a href="#" @click.prevent="exportRecord()" class="btn btn-primary" id="exportRecord" v-show="flag_export == 1">
                            <i class="fa fa-file-excel"></i> Exportar excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-portlet__body kt-portlet__body--fit" @click="manageActions">
            <!--begin: Datatable -->
            <div class="kt-datatable"></div>
            <!--end: Datatable -->
        </div>
    </div>
    <!--end::Portlet-->
</template>

<script>
    import EventBus from '../event-bus';
    export default {
        props: {
            url_delete: {
                type: String,
                default: ''
            },
            url_export_record: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                datatable: undefined,
                data: [],
                warehouse_type_id: '',
				flag_export: false,
                show_results: false,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                const data = response.data;
                this.show_results = true;
                this.data = data;

                if ( this.datatable == undefined ) {
                    this.fillTableX(data);
                } else {
                    this.datatable.destroy();
                    this.fillTableX(data);
                    this.datatable.load();
                }

                EventBus.$emit('loading', false);
            }.bind(this));

            EventBus.$on('refresh_table', function(data) {
                this.data = data;
                this.datatable.destroy();
                this.fillTableX(data);
                this.datatable.load();
            }.bind(this));
        },
        watch: {
            datatable: function() {
				this.flag_export = this.datatable != undefined ? true : false;
			}
        },
        computed: {

        },
        methods: {
			exportRecord: function() {
                EventBus.$emit('loading', true);

                const warehouse_type_id = this.$store.state.warehouse_type_id;

                axios.post(this.url_export_record, {
                    warehouse_type_id,
                    export: true
                }, {
                    responseType: 'blob',
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('loading', false);

                    let creation_date = new Date();
                    let date = creation_date.getDate();
                    let month = creation_date.getMonth();
                    let year = creation_date.getFullYear();
                    creation_date = year+'-'+month+'-'+date;

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-articulos-'+creation_date+'.xls'); //or any other extension
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            },
            fillTableX: function(data) {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: data
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
                    pagination: false,

                    translate: {
                        records: {
                            processing: 'Espere porfavor...',
                            noRecords: 'No hay registros'
                        },
                        toolbar: {
                            pagination: {
                                items: {
                                    default: {
                                        first: 'Primero',
                                        prev: 'Anterior',
                                        next: 'Siguiente',
                                        last: 'Último',
                                        more: 'Más páginas',
                                        input: 'Número de página',
                                        select: 'Seleccionar tamaño de página'
                                    },
                                    info: 'Mostrando {{start}} - {{end}} de {{total}} registros'
                                }
                            }
                        }
                    },

                    rows: {
                        autoHide: false,
                    },

                    // columns definition
                    columns: [
                        {
                            field: 'id',
                            title: 'ID',
                            width: 0,
                            textAlign: 'center',
                            overflow: 'hidden',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'code_product',
                            title: 'Código',
                            width: 50,
                            textAlign: 'center',
                        },
                        {
                            field: 'name',
                            title: 'Descripción',
                            width: 120,
                        },
                        {
                            field: 'sale_price',
                            title: 'Precio de Venta',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'cost_price',
                            title: 'Precio de Compra',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'stock',
                            title: 'Stock',
                            width: 50,
                            textAlign: 'center',
                        },
                        {
                            field: 'expiration_date',
                            title: 'Fecha de Expiracion',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'clase',
                            title: 'Clase',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'marca',
                            title: 'Marca',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'family',
                            title: 'Familia',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'warehouse_type_name',
                            title: 'Botica',
                            width: 120,
                            textAlign: 'center',
                        },
                        {
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 80,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
                            // class: 'td-sticky',
                            template: function(row) {
                                let actions = '<div class="actions">';
                                actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                                actions += '<i class="la la-edit"></i>';
                                actions += '</a>';
                                actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                                actions += '<i class="la la-trash"></i>';
                                actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        },
                    ]
                });

                this.datatable.columns('id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('delete') ) {
                    event.preventDefault();
                    const id = $(event.target).parents('tr').find('td[data-field="id"] span').html();
                    const index = this.data.findIndex(item => item.id == id);

                    Swal.fire({
                        title: '¡Cuidado!',
                        text: '¿Seguro que desea eliminar el registro?',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then(result => {
                        EventBus.$emit('loading', true);
                        this.data.splice(index, 1);

                        this.datatable.destroy();

                        if ( result.value ) {
                            axios.post(this.url_delete, { id }).then(response => {
                                EventBus.$emit('loading', false);
                                Swal.fire({
                                    title: '¡Ok!',
                                    text: 'Se ha eliminado correctamente',
                                    type: "success",
                                    heightAuto: false,
                                });
                                this.fillTableX(this.data);
                                this.datatable.load();
                            }).catch(error => {
                                EventBus.$emit('loading', false);
                                console.log(error);
                                console.log(error.response);
                            });
                        } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                            EventBus.$emit('loading', false);
                        }
                    });
                } else if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    const id = $(event.target).parents('tr').find('td[data-field="id"] span').html();
                    const index = this.data.findIndex(item => item.id == id);
                    const item = this.data[index];
                    EventBus.$emit('edit_modal', item);
                }
            },
        }
    };
</script>