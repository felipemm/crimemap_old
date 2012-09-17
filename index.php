<?php
    //inicia ou resume a sessão
    session_start();
    /* if not logged in then back to login page */
    if(!isset($_SESSION['is_successful_login']) || $_SESSION['is_successful_login'] == false) {
        header ('location: login.php');
        exit;
    }

    include('include/config.php');
    // Conexao ao DB. Não mexer.
    $conexao = mysql_connect(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD) or die($msg[0]);
    mysql_select_db (MYSQL_DATABASE, $conexao) or die($msg[1]);
    mysql_query("SET NAMES 'utf8'");
    mysql_query('SET character_set_connection=utf8');
    mysql_query('SET character_set_client=utf8');
    mysql_query('SET character_set_results=utf8');

    //variáveis de sessão
    $user_name = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];
    $admin_flag = $_SESSION['admin_flag'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <title>Statísticas dentro de uma região</title>

        <!-- ** CSS ** -->
        <!-- base library -->
        <link rel="stylesheet" type="text/css" href="http://cdn.sencha.io/ext-3.3.0/resources/css/ext-all.css" />
        <!-- overrides to base library -->
        <link rel="stylesheet" type="text/css" href="css/main.css" />

        <!-- ** Javascript ** -->
        <script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAkAwg-hRBqPHfyG2fFrg0BhSNIPjrEOnwV97tPN7ZF154By4k4xQ7XOYOuTzJKQgklOxro6q_NrwE2A"></script>
        <script type="text/javascript" src="js/jquery-1.4.2.js"></script>
        <!-- ExtJS library: base/adapter -->
        <script type="text/javascript" src="http://cdn.sencha.io/ext-3.3.0/adapter/ext/ext-base.js"></script>
        <script type="text/javascript" src="http://cdn.sencha.io/ext-3.3.0/adapter/jquery/ext-jquery-adapter.js"></script>
        <!-- ExtJS library: all widgets -->
        <script type="text/javascript" src="http://cdn.sencha.io/ext-3.3.0/ext-all.js"></script>
        <!-- overrides to library -->
        <!-- extensions -->
        <script type="text/javascript" src="js/GMapPanel.js"></script>
        <script type="text/javascript" src="js/examples.js"></script>
        <!-- page specific -->
        <script type="text/javascript">
            //-------- CONFIG DO EXT ------------//
            Ext.chart.Chart.CHART_URL = 'http://cdn.sencha.io/ext-3.3.0/resources/charts.swf';
            Ext.BLANK_IMAGE_URL = 'http://cdn.sencha.io/ext-3.3.0/resources/images/default/s.gif';
            //-----------------------------------//

            Ext.onReady(function(){
                //-------- VARIÁVEIS GLOBAIS --------//
                var map; //armazena o objeto do mapa do gmaps
                var geocoder; //objeto para retornar endereço a partir de coordenadas
                var place; //variável do gmaps que armazena os dados geográficos retornados do geocoder
                var marker; //variável que armazena os dados de um marcador
                var gmarkers = []; //array que ira armazenar todos os marcadores
                var bounds = new GLatLngBounds(); //pan and zoom to fit
                var nrc; //container principal - norte -- toolbar no topo
                var crc; //container principal - central -- conteúdo
                var wrc; //container principal - oeste -- menu lateral
                var month1Id; //variável usada nos gráficos para relacionar meses
                var month2Id; //variável usada nos gráficos para relacionar meses
                //-----------------------------------//


                //cria o painel principal com as divisões da tela e renderiza no body da página
                new Ext.Panel({
                    id:'mainPanel',
                    header:false,
                    renderTo: document.body,
                    width: '100%',
                    height: 650,
                    margins: 'auto',
                    layout:'border',
                    defaults: {
                        collapsible: false,
                        split: false,
                        bodyStyle: 'padding:1px'
                    },
                    items: [{
                            id:'north-region-container',
                            header:false,
                            region: 'north',
                            height: 30,
                            minSize: 75,
                            maxSize: 250,
                            cmargins: '0 0 0 0'
                        },{
                            id:'west-region-container',
                            header:true,
                            region:'west',
                            title: 'Menu',
                            margins: '0 0 0 0',
                            cmargins: '0 0 0 0',
                            width: 200,
                            minSize: 100,
                            maxSize: 250
                        },{
                            id:'center-region-container',
                            header: false,
                            region:'center',
                            margins: 'auto'
                    }]
                });

                //define as variáveis de cada painel para serem usadas
                nrc = Ext.getCmp('north-region-container');
                crc = Ext.getCmp('center-region-container');
                wrc = Ext.getCmp('west-region-container');

                //limpa os paineis
                nrc.removeAll();
                crc.removeAll();
                wrc.removeAll();


                //------------ TOOLBAR TOPO ---------//
                //cria o menu
                var tb = new Ext.Toolbar();
                //Voltar para o menu pincipal
                tb.add({text:'Menu Principal',id:'btnHome',handler:btnHomeClick});
                //gráficos a partir de uma região
                tb.add({text:'Mapa',id:'btnMapViewer',handler:btnMapViewerClick});
                //gráficos de tendências
                tb.add({text:'Gráficos',id:'btnCharts',handler:btnChartsClick});
                //gráficos a partir de uma região
                tb.add({text:'Região',id:'btnMapRadious',handler:btnMapRadiousClick});
                //filler para a toolbar
                tb.add({xtype:'tbfill'}); 

                //adiciona a toolbar no painel norte
                nrc.add(tb);
                //da um refresh no browser
                nrc.doLayout();
                //-----------------------------------//


                //------- PAGINA: MENU PRINCIPAL-----//
                //Evento onClick para o botão btnHome
                function btnHomeClick(){
                    //limpa os containers
                    crc.removeAll();
                    wrc.removeAll();

                    //cria o menu lateral
                    var tb = new Ext.Toolbar({
                        layout:'menu',
                        items:[{
                            text: 'Novo Incidente',
                            id:'menuNewIncident',
                            handler:menuNewIncidentClick,
                            width:'100%'
                        }]
                    });

                    //checa flag de administrador do usuário para criar o menu
                    //para o painel administrativo
                    var adm = Ext.get('admin_flag').getValue(true);
                    if (adm == 1){
                        tb.add({
                            text: 'Painel de Administração',
                            id:'menuAdminPanel',
                            handler:menuAdminPanelClick,
                            width:'100%'
                        });
                    }

                    tb.add({
                        text: 'Logout',
                        id:'menuLogout',
                        handler:menuLogoutClick,
                        width:'100%'
                    });

                    //insere o menu na barra lateral
                    wrc.add(tb);
                    wrc.doLayout();


                    //popula uma store com os pontos cadastrados pelo usuario
                    var store = new Ext.data.Store({
                        autoDestroy: true,
                        proxy: new Ext.data.HttpProxy({
                            url: "ajax/get_user_points.php",
                            waitMsg: "Buscando..."
                        }),
                        reader: new Ext.data.JsonReader({
                            root:'data'
                        },[
                            "event_id",
                            "crime_category_name",
                            "event_date",
                            "event_time",
                            "district_name",
                            "event_txt",
                            "status_name"
                        ])
                     });
                     store.load();

                    //cria o grid com os dados
                    var grid = new Ext.grid.GridPanel({
                        title:'Pontos cadastrados pelo usuário',
                        width:'100%',
                        height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                        iconCls: 'icon-grid',
                        store: store,
                        colModel: new Ext.grid.ColumnModel({
                           defaults:{
                               width:120,
                               sortable:true
                           },
                           columns:[
                               {header:'ID',dataIndex:'event_id',id:'event_id',sortable:true},
                               {header:'Categoria',dataIndex:'crime_category_name'},
                               {header:'Data',dataIndex:'event_date'},
                               {header:'Hora',dataIndex:'event_time'},
                               {header:'Bairro',dataIndex:'district_name'},
                               {header:'Descrição',dataIndex:'event_txt'},
                               {header:'Status',dataIndex:'status_name'}
                           ]
                        }),
                        viewConfig:{
                            forceFit:true
                        },
                        sm: new Ext.grid.RowSelectionModel({singleSelect:true})
                    });

                    //atualiza o container central com o grid
                    crc.add(grid);
                    crc.doLayout();
                }

                //evento para controle onClick para novos incidentes
                function menuNewIncidentClick(){
                    //cria o painel onde irá ficar o gmaps
                    var mappanel = new Ext.Panel({
                        width: '100%',
                        height: wrc.getInnerHeight() + wrc.getFrameHeight(),
                        layout:'border',
                        items:[{
                            //container para o gmaps
                            id:'new_incident_map',
                            xtype: 'gmappanel',
                            width:'100%',
                            region: 'center',
                            zoomLevel: 14,
                            gmapType: 'map',
                            mapConfOpts: ['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging'],
                            mapControls: ['GSmallMapControl','GMapTypeControl','NonExistantControl'],
                            setCenter: {
                                lat: -22.9062436,
                                lng:-47.0616158
                            },
                            mapEvents:{
                                'click':function(overlay, latlng){
                                    map = Ext.getCmp('new_incident_map').getMap();
                                    geocoder = new GClientGeocoder();
                                    map.panTo(latlng);
                                    geocoder.getLocations(latlng, getNewIncidentAddress);
                                }
                            }
                        }]
                    });

                    //atualiza o container central com o mapa
                    crc.removeAll();
                    crc.add(mappanel);
                    crc.doLayout();
                }

                //função usada no onClick do mapa para criar o marcador e armazenar os dados
                //do geocoder (endereço e bairro)
                function getNewIncidentAddress(response){
                    if (!response || response.Status.code != 200) {
                        alert("Status Code:" + response.Status.code);
                    } else {
                        place = response.Placemark[0];
                        var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
                        marker = new GMarker(point);
                        Ext.MessageBox.show({
                            title:'Confirma endereço?',
                            msg: place.AddressDetails.Country.AdministrativeArea.Locality.DependentLocality.Thoroughfare.ThoroughfareName,
                            buttons: Ext.MessageBox.YESNO,
                            fn: newMarkerAddressConfirmClick,
                            animEl: 'mb4',
                            icon: Ext.MessageBox.QUESTION
                        });
                    }
                }

                //função chamada quando o usuário confirma o endereço retornado
                //após o evento onClick do mapa. Caso o usuário tenha confirmado
                //o endereço, uma janela para cadastro do incidente irá aparecer
                function newMarkerAddressConfirmClick(btn){
                    if (btn == 'yes'){
                        //store usado para retornar as categorias principais do
                        //sistema para ser usado no combobox
                        var storeCategorias = new Ext.data.Store({
                            proxy: new Ext.data.HttpProxy({url: 'ajax/get_categorias.php'}),
                            reader: new Ext.data.JsonReader({
                                root:'data',
                                fields:['id','displayText']
                            })
                        });
                        storeCategorias.load();

                        //store usado para retornar os bairros
                        //da cidade para ser usado no combobox
                        var storeBairro = new Ext.data.Store({
                            proxy: new Ext.data.HttpProxy({url: 'ajax/get_bairros.php'}),
                            reader: new Ext.data.JsonReader({
                                root:'data',
                                fields:['id','displayText']
                            })
                        });
                        storeBairro.load();

                        //cria o formulario de cadastro do incidente
                        var form = new Ext.form.FormPanel({
                            labelWidth: 90,
                            frame:true,
                            title: 'Cadastro de incidentes',
                            bodyStyle:'padding:5px 5px 0',
                            width: '100%',
                            defaults: {width: '100%'},
                            defaultType: 'textfield',
                            items:[{
                                xtype: 'compositefield',
                                fieldLabel: 'Data/Hora',
                                combineErrors: false,
                                items: [{
                                    name:'formDataEvento',
                                    fieldLabel:'Data',
                                    xtype:'datefield'
                                },{
                                    name : 'formHoraEvento',
                                    xtype: 'numberfield',
                                    width: 48,
                                    allowBlank: false
                                },{
                                    xtype: 'displayfield',
                                    value: 'hrs'
                                },{
                                    name : 'formMinutoEvento',
                                    xtype: 'numberfield',
                                    width: 48,
                                    allowBlank: false
                                },{
                                    xtype: 'displayfield',
                                    value: 'mins'
                                }]
                            },{
                                id:'formCategoria',
                                fieldLabel:'Categoria',
                                xtype:'combo',
                                width:'100%',
                                hiddenName:'formCategoriaId',
                                hiddenValue:0,
                                typeAhead: true,
                                triggerAction: 'all',
                                mode:'local',
                                iconCls: 'no-icon',
                                store: storeCategorias,
                                valueField:'id',
                                displayField: 'displayText',
                                allowBlanks:false,
                                listeners:{
                                    'select':function(combo,record,index){
                                        var subCombo = Ext.getCmp('formSubCategoria');
                                        subCombo.disable();
                                        subCombo.setValue();
                                        subCombo.store.removeAll();
                                        subCombo.store.reload({
                                            params: {
                                                id: record.id
                                            }
                                        })
                                        subCombo.enable();
                                        form.doLayout();
                                    }
                                }
                            },{
                                id:'formSubCategoria',
                                fieldLabel:'Sub-Categoria',
                                xtype:'combo',
                                width:'100%',
                                hiddenName:'formSubCategoriaId',
                                hiddenValue:0,
                                typeAhead: true,
                                triggerAction: 'all',
                                mode:'local',
                                iconCls: 'no-icon',
                                store: getSubCategoriaStore(),
                                valueField:'id',
                                displayField: 'displayText',
                                disabled:true,
                                allowBlanks:false
                            },{
                                id:'formBairro',
                                fieldLabel:'Bairro',
                                xtype:'combo',
                                width:'100%',
                                hiddenName:'formBairroId',
                                hiddenValue:0,
                                typeAhead: true,
                                triggerAction: 'all',
                                mode:'local',
                                iconCls: 'no-icon',
                                store: storeBairro,
                                valueField:'id',
                                displayField: 'displayText',
                                allowBlanks:false
                            },{
                                id:'formDescricao',
                                fieldLabel:'Descrição',
                                xtype:'textarea',
                                grow:false,
                                allowBlank:false,
                                maxLength:1000,
                                minLength:50,
                                width:'95%'
                            }],
                            buttons:[{
                                id:'btnFormSave',
                                xtype:'button',
                                text:'Salvar',
                                handler:function(){
                                    form.getForm().submit({
                                        params:{
                                            pointLat:place.Point.coordinates[1],
                                            pointLng:place.Point.coordinates[0]
                                        },
                                        url:'ajax/save_incident.php',
                                        success: function(form, action) {
                                            Ext.Msg.alert('Sucesso!', action.result.msg);
                                            map.addOverlay(marker);
                                            win.close();
                                        },
                                        failure: function(form, action) {
                                            switch (action.failureType) {
                                                case Ext.form.Action.CLIENT_INVALID:
                                                    Ext.Msg.alert('Erro!', 'Todos os campos devem ser preenchidos');
                                                    break;
                                                case Ext.form.Action.CONNECT_FAILURE:
                                                    Ext.Msg.alert('Erro!', 'Houve um erro de conexão com o servidor. Por favor tente novamente');
                                                    break;
                                                case Ext.form.Action.SERVER_INVALID:
                                                   Ext.Msg.alert('Erro!', action.result.msg);
                                           }
                                        }
                                    });
                                }
                            },{
                                id:'btnFormCancel',
                                xtype:'button',
                                text:'Cancelar',
                                handler:function(){
                                    win.close();
                                }
                            }]
                        });
                        Ext.getCmp('formBairro').setValue(place.AddressDetails.Country.AdministrativeArea.Locality.DependentLocality.DependentLocalityName);

                        var win = new Ext.Window({
                            width:400,
                            //height:600,
                            modal:true,
                            border:false,
                            closable:false,
                            items:[form]
                        });
                        win.show();
                        win.center();
                    }
                }

                function getSubCategoriaStore(){
                    var subCatStore = new Ext.data.Store({
                        proxy: new Ext.data.HttpProxy({url: 'ajax/get_sub_categorias.php'}),
                        reader: new Ext.data.JsonReader({
                            root:'data',
                            fields:['id','displayText']
                        })
                    });
                    return subCatStore;
                }

                //evento para controle onClick do logout
                function menuLogoutClick(){
                    document.location.href = 'logout.php';
                }

                //evento para controle onClick do Painel de administrador
                function menuAdminPanelClick(){
                    //cria o menu
                    var tb = new Ext.Toolbar();
                    tb.add({text:'Novos Incidentes',id:'btnAdminNewIncident',handler:btnAdminNewIncidentClick});
                    tb.add({text:'Novos Usuários',id:'btnAdminNewUser',handler:btnAdminNewUserClick});
                    tb.add({xtype:'tbfill'});

                    
                    var adminPanel = new Ext.Panel({
                        width: '100%',
                        height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                        layout:'border',
                        items:[{
                            //container para o gráfico
                            id:'admin_menu',
                            header:false,
                            region: 'north',
                            width: '100%',
                            items:[tb]
                        },{
                            //container para o endereço
                            id:'admin_grid',
                            header:false,
                            region: 'center',
                            width: '100%'
                        }]
                    });

                    crc.removeAll();
                    crc.add(adminPanel);
                    crc.doLayout();
                }

                function btnAdminNewIncidentClick(){
                    var selRecordStore;

                    var incidentStore = new Ext.data.Store({
                        proxy: new Ext.data.HttpProxy({url: 'ajax/get_new_points.php'}),
                        reader: new Ext.data.JsonReader({
                            root:'data',
                            fields:[
                                'event_id',
                                'user_name',
                                'crime_category_name',
                                'event_date',
                                'event_time',
                                'district_name',
                                'event_txt',
                                'status_name'
                            ]
                        })
                    });
                    incidentStore.load();

                    //cria o grid com os dados
                    var grid = new Ext.grid.GridPanel({
                        title:'Pontos cadastrados pelo usuário pendentes de aprovação/rejeição',
                        width:'100%',
                        height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                        iconCls: 'icon-grid',
                        store: incidentStore,
                        colModel: new Ext.grid.ColumnModel({
                           defaults:{
                               width:120,
                               sortable:true
                           },
                           columns:[
                               {header:'ID',dataIndex:'event_id',id:'event_id',sortable:true},
                               {header:'Usuário',dataIndex:'user_name'},
                               {header:'Categoria',dataIndex:'crime_category_name'},
                               {header:'Data',dataIndex:'event_date'},
                               {header:'Hora',dataIndex:'event_time'},
                               {header:'Bairro',dataIndex:'district_name'},
                               {header:'Descrição',dataIndex:'event_txt'},
                               {header:'Status',dataIndex:'status_name'}
                           ]
                        }),
                        viewConfig:{
                            forceFit:true
                        },
                        sm: new Ext.grid.RowSelectionModel({
                            singleSelect:true,
                            listeners: {
                                rowselect: function(smObj, rowIndex, record) {
                                    selRecordStore = record;
                                    if(selRecordStore != undefined){
                                        Ext.getCmp('menuAdminApprove').enable();
                                        Ext.getCmp('menuAdminReject').enable();
                                    } else {
                                        Ext.getCmp('menuAdminApprove').disable();
                                        Ext.getCmp('menuAdminReject').disable();
                                    }
                                }
                            }
                        }),
                        tbar:[
                            new Ext.Toolbar.Button({
                                id:'menuAdminApprove',
                                text: 'Aprovar',
                                disabled:true,
                                handler: function(){
                                    Ext.getCmp('menuAdminApprove').disable();
                                    Ext.getCmp('menuAdminReject').disable();
                                    //alert(selRecordStore.data['event_id']);
                                    AdminIncidentUpdate(selRecordStore.data['event_id'], 1);
                                    incidentStore.reload();
                                    grid.doLayout();
                                }
                            }),
                            new Ext.Toolbar.Button({
                                id:'menuAdminReject',
                                text: 'Rejeitar',
                                disabled:true,
                                handler: function(){
                                    Ext.getCmp('menuAdminApprove').disable();
                                    Ext.getCmp('menuAdminReject').disable();
                                    AdminIncidentUpdate(selRecordStore.data['event_id'], 4);
                                    incidentStore.reload();
                                    grid.doLayout();
                                }
                            })]
                    });

                    //atualiza o container central com o grid
                    var adminPnl = Ext.getCmp('admin_grid');
                    adminPnl.removeAll();
                    adminPnl.add(grid);
                    adminPnl.doLayout();
                }

                function AdminIncidentUpdate(id, status){
                    Ext.Ajax.request({
                        url : 'ajax/update_point.php',
                        method: 'POST',
                        params:{
                            id: id,
                            status: status
                        },
                        success: function(result, request){
                        },
                        failure: function ( result, request ) {
                        }
                    });
                }

                function btnAdminNewUserClick(){
                    var selRecordStore;

                    var userStore = new Ext.data.Store({
                        proxy: new Ext.data.HttpProxy({url: 'ajax/get_new_users.php'}),
                        reader: new Ext.data.JsonReader({
                            root:'data',
                            fields:[
                                'user_id',
                                'user_name',
                                'user_real_name',
                                'user_email',
                                'admin_flag'
                            ]
                        })
                    });
                    userStore.load();

                    //cria o grid com os dados
                    var grid = new Ext.grid.GridPanel({
                        title:'Novos usuários pendentes de aprovação/rejeição',
                        width:'100%',
                        height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                        iconCls: 'icon-grid',
                        store: userStore,
                        colModel: new Ext.grid.ColumnModel({
                           defaults:{
                               width:120,
                               sortable:true
                           },
                           columns:[
                               {header:'ID',dataIndex:'user_id',id:'user_id',sortable:true},
                               {header:'Usuário',dataIndex:'user_name'},
                               {header:'Nome',dataIndex:'user_real_name'},
                               {header:'e-Mail',dataIndex:'user_email'},
                               {header:'Admin?',dataIndex:'admin_flag'}
                           ]
                        }),
                        viewConfig:{
                            forceFit:true
                        },
                        sm: new Ext.grid.RowSelectionModel({
                            singleSelect:true,
                            listeners: {
                                rowselect: function(smObj, rowIndex, record) {
                                    selRecordStore = record;
                                    if(selRecordStore != undefined){
                                        Ext.getCmp('menuAdminApprove').enable();
                                        Ext.getCmp('menuAdminReject').enable();
                                    } else {
                                        Ext.getCmp('menuAdminApprove').disable();
                                        Ext.getCmp('menuAdminReject').disable();
                                    }
                                }
                            }
                        }),
                        tbar:[
                            new Ext.Toolbar.Button({
                                id:'menuAdminApprove',
                                text: 'Aprovar',
                                disabled:true,
                                handler: function(){
                                    Ext.getCmp('menuAdminApprove').disable();
                                    Ext.getCmp('menuAdminReject').disable();
                                    //alert(selRecordStore.data['event_id']);
                                    AdminUserUpdate(selRecordStore.data['user_id'], 1);
                                    userStore.reload();
                                    grid.doLayout();
                                }
                            }),
                            new Ext.Toolbar.Button({
                                id:'menuAdminReject',
                                text: 'Rejeitar',
                                disabled:true,
                                handler: function(){
                                    Ext.getCmp('menuAdminApprove').disable();
                                    Ext.getCmp('menuAdminReject').disable();
                                    AdminUserUpdate(selRecordStore.data['user_id'], 4);
                                    userStore.reload();
                                    grid.doLayout();
                                }
                            })]
                    });

                    //atualiza o container central com o grid
                    var adminPnl = Ext.getCmp('admin_grid');
                    adminPnl.removeAll();
                    adminPnl.add(grid);
                    adminPnl.doLayout();
                    
                }

                function AdminUserUpdate(id, status){
                    Ext.Ajax.request({
                        url : 'ajax/update_user.php',
                        method: 'POST',
                        params:{
                            id: id,
                            status: status
                        },
                        success: function(result, request){
                        },
                        failure: function ( result, request ) {
                        }
                    });
                }
                //-----------------------------------//



                //Evento onClick para o botão btnMapViewer
                function btnMapViewerClick(){
                    var tree = new Ext.tree.TreePanel({
                        title: 'Categorias',
                        height: wrc.getInnerHeight(),
                        width: '100%',
                        useArrows:true,
                        autoScroll:true,
                        animate:true,
                        enableDD:true,
                        containerScroll: true,
                        rootVisible: false,
                        frame: true,
                        root: {
                            nodeType: 'async',
                            text: 'Ext JS',
                            draggable: false,
                            id: 'source'
                        },
                        dataUrl: 'ajax/get_category_tree.php',
                        listeners: {
                            'checkchange': function(node, checked){
                                if(checked){
                                    for (var i=0; i<gmarkers.length; i++) {
                                      if (gmarkers[i].myCategory == node.id) {
                                        gmarkers[i].show();
                                      }
                                    }
                                }else{
                                    for (var i=0; i<gmarkers.length; i++) {
                                      if (gmarkers[i].myCategory == node.id) {
                                        gmarkers[i].hide();
                                      }
                                    }
                                }
                            }
                        },
                        buttons:[{
                            text:'Marcar Todos',
                            handler:function(){
                                toggleCheck(tree.root,true);
                            }
                        },{
                            text:'Desmarcar Todos',
                            handler:function(){
                                toggleCheck(tree.root,false);
                            }
                        }]
                    });
                    wrc.removeAll();
                    wrc.add(tree);
                    wrc.doLayout();
                    tree.getRootNode().expand(true);


                    var markerStore = new Ext.data.Store({
                        proxy: new Ext.data.HttpProxy({url: 'ajax/get_viewer_points.php'}),
                        reader: new Ext.data.JsonReader({
                            root:'data',
                            fields:[
                                'event_id',
                                'event_date',
                                'event_time',
                                'lat',
                                'lng',
                                'crime_category_id',
                                'crime_category_name',
                                'district_id',
                                'district_name'
                            ]
                        })
                    });

                    var mappanel = new Ext.Panel({
                        width: '100%',
                        height: wrc.getInnerHeight() + wrc.getFrameHeight(),
                        layout:'border',
                        items:[{
                            //container para o gmaps
                            id:'viewer_map',
                            xtype: 'gmappanel',
                            width:'100%',
                            region: 'center',
                            zoomLevel: 14,
                            gmapType: 'map',
                            mapConfOpts: ['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging'],
                            mapControls: ['GSmallMapControl','GMapTypeControl','NonExistantControl'],
                            setCenter: {
                                lat: -22.9062436,
                                lng:-47.0616158
                            },
                            mapEvents:{
                                'click':function(overlay, latlng){
                                    map.panTo(latlng);
                                }
                            }
                        }]
                    });

                    //atualiza o container central com o mapa
                    crc.removeAll();
                    crc.add(mappanel);
                    crc.doLayout();

                    //armazena o objeto gmaps na variável
                    map = Ext.getCmp('viewer_map').getMap();


                    //no evento onLoad da store, ira adicionar os marcadores no mapa
                    markerStore.on('load', function () {
                        markerStore.data.each(function(){
                            var point = new GLatLng(this.data['lat'], this.data['lng']);
                            marker = new GMarker(
                                point,
                                {
                                    icon: getMarkerIcons(this.data['crime_category_id']),
                                    title: this.data['crime_category_name'],
                                    autoPan: true,
                                    clickable: true,
                                    draggable: false
                                }
                            );
                            marker.myCategory = this.data['crime_category_id']+'_'+this.data['crime_category_name'];
                            gmarkers.push(marker);
                            map.addOverlay(marker);
                        });
                    });
                    //carrega os marcadores
                    markerStore.load();
                }

                //function to check/uncheck all the child node.
                function toggleCheck(node,isCheck) {
                    if(node){
                        var args=[isCheck];
                        node.cascade(function(){
                            c=args[0];
                            this.ui.toggleCheck(c);
                            this.attributes.checked=c;
                        },null,args);
                    }
                }

                function getMarkerIcons(id){
                    var Icon = new GIcon();
                    Icon.image = "img/marker/" + id + ".png";
                    Icon.iconSize = new GSize(32,32);
                    Icon.iconAnchor = new GPoint(12,12);
                    Icon.infoWindowAnchor = new GPoint(12,24);
                    return Icon;
                }

                //Evento on click para o botão btnCharts
                function btnChartsClick(){
                    wrc.removeAll();
                    crc.removeAll();

                    var cbx = new Ext.form.ComboBox({
                        width:'100%',
                        typeAhead: true,
                        triggerAction: 'all',
                        emptyText: 'selecione a opção...',
                        id:'chartSelect',
                        mode:'local',
                        iconCls: 'no-icon',
                        store: new Ext.data.ArrayStore({
                            fields:['myId','displayText'],
                            data:[
                                [1,'Ocorrências no mês'],
                                [2,'Ocorrências em cada hora'],
                                [3,'Ocorrências no ano'],
                                [4,'Ocorrências por Bairro'],
                                [5,'Ocorrências por Categoria'],
                                [6,'Relação entre meses']
                            ]
                        }),
                        valuefield:'myId',
                        displayField: 'displayText',
                        listeners:{
                            'select': function(combo,record,index){
                                crc.removeAll();
                                
                                $.ajax({
                                    url: 'ajax/graph_data.php?id='+record.data.myId,
                                    method: 'GET',
                                    dataType: 'json',
                                    success: function(series){
                                        //armazena o json no formato correto
                                        var store = new Ext.data.JsonStore(series);


                                        var optionPanel = new Ext.Panel({
                                            header:false,
                                            border:false,
                                            id:'optionPanel',
                                            width: '100%',
                                            height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                                            layout: 'anchor',
                                            frame:false
                                        });

                                        wrc.remove('optionPanel');
                                        wrc.add(optionPanel);
                                        wrc.doLayout();

                                        switch (record.data.myId) {
                                            case 1:
                                                //cria o combobx de seleção do mês
                                                var cbxMonth = new Ext.form.ComboBox({
                                                    width:'100%',
                                                    typeAhead: true,
                                                    triggerAction: 'all',
                                                    emptyText: 'selecione a opção...',
                                                    id:'monthSelect',
                                                    mode:'local',
                                                    iconCls: 'no-icon',
                                                    store:store,
                                                    valuefield:'calendar_month',
                                                    displayField: 'month_name',
                                                    listeners:{
                                                        'select':function(combo,record,index){
                                                            //carrega os dados do gráfico
                                                            var monthStore = new Ext.data.Store({
                                                                proxy: new Ext.data.HttpProxy({url: 'ajax/graph_by_month.php?month_id='+record.data.calendar_month}),
                                                                reader: new Ext.data.JsonReader({
                                                                    root:'data',
                                                                    fields:['calendar_date','crime_cnt']
                                                                })
                                                            });
                                                            monthStore.load();

                                                            //cria o objeto do grafico
                                                            var chart = new Ext.Panel({
                                                                header:false,
                                                                width: '100%',
                                                                height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                                                                layout: 'anchor',
                                                                frame:false,
                                                                items: {
                                                                    xtype: 'linechart',
                                                                    store: monthStore,
                                                                    xField: 'calendar_date',
                                                                    yField: 'crime_cnt'
                                                                }
                                                            });

                                                            //renderiza o gráfico no painel central
                                                            crc.removeAll();
                                                            crc.doLayout();
                                                            crc.add(chart);
                                                            crc.doLayout();
                                                        }
                                                    }
                                                });

                                                //atualiza o menu lateral com o novo combobx
                                                optionPanel.add(cbxMonth);
                                                optionPanel.doLayout();
                                                break;
                                            case 2:
                                                //cria o combobx de seleção para o grafico de horas
                                                var cbxHour = new Ext.form.ComboBox({
                                                    width:'100%',
                                                    typeAhead: true,
                                                    triggerAction: 'all',
                                                    emptyText: 'selecione a opção...',
                                                    id:'hourSelect',
                                                    mode:'local',
                                                    iconCls: 'no-icon',
                                                    store: new Ext.data.ArrayStore({
                                                        fields:['myId','displayText'],
                                                        data:[
                                                            ['last_3_months','Nos últimos 3 meses'],
                                                            ['last_6_months','Nos últimos 6 meses'],
                                                            ['last_12_months','Nos últimos 12 meses']
                                                        ]
                                                    }),                                                  
                                                    valuefield:'myId',
                                                    displayField: 'displayText',
                                                    listeners:{
                                                        'select':function(combo,record,index){
                                                            //carrega os dados do gráfico
                                                            var hourStore = new Ext.data.Store({
                                                                proxy: new Ext.data.HttpProxy({url: 'ajax/graph_by_hour.php?type='+record.data.myId}),
                                                                reader: new Ext.data.JsonReader({
                                                                    root:'data',
                                                                    fields:['id','crime_cnt']
                                                                })
                                                            });
                                                            hourStore.load();
                                                            
                                                            //cria o objeto do grafico
                                                            var chart = new Ext.Panel({
                                                                header:false,
                                                                width: '100%',
                                                                height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                                                                layout: 'anchor',
                                                                frame:false,
                                                                items: {
                                                                    xtype: 'columnchart',
                                                                    store: hourStore,
                                                                    xField: 'id',
                                                                    series: [{
                                                                        type: 'column',
                                                                        yField: 'crime_cnt',
                                                                        style: {
                                                                            image:'bar.gif',
                                                                            mode: 'stretch',
                                                                            color:0x99BBE8
                                                                        }
                                                                    }]
                                                                }
                                                            });

                                                            //renderiza o gráfico no painel central
                                                            crc.removeAll();
                                                            crc.doLayout();
                                                            crc.add(chart);
                                                            crc.doLayout();
                                                        }
                                                    }
                                                });

                                                //atualiza o menu lateral com o novo combobx
                                                optionPanel.add(cbxHour);
                                                optionPanel.doLayout();
                                                break;
                                            case 3:
                                                //cria o objeto do grafico
                                                var chart = new Ext.Panel({
                                                    title: 'sample',
                                                    width: '100%',
                                                    height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                                                    layout: 'anchor',
                                                    frame:false,
                                                    items: {
                                                        xtype: 'piechart',
                                                        store: store,
                                                        categoryField: 'calendar_date',
                                                        dataField: 'crime_cnt',
                                                        extraStyle:{
                                                            legend:{
                                                                display: 'bottom',
                                                                padding: 5,
                                                                font:{
                                                                    family: 'Tahoma',
                                                                    size: 13
                                                                }
                                                            }
                                                        }
                                                    }
                                                });

                                                //renderiza o gráfico no painel central
                                                crc.removeAll();
                                                crc.doLayout();
                                                crc.add(chart);
                                                crc.doLayout();
                                                break;
                                            case 4:
                                                //cria o objeto do grafico
                                                var chart = new Ext.Panel({
                                                    header:false,
                                                    width: '100%',
                                                    height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                                                    layout: 'anchor',
                                                    frame:false,
                                                    items: {
                                                        xtype: 'barchart',
                                                        store: store,
                                                        yField: 'district_name',
                                                        xField: 'crime_cnt'
                                                    }
                                                });

                                                //renderiza o gráfico no painel central
                                                crc.removeAll();
                                                crc.doLayout();
                                                crc.add(chart);
                                                crc.doLayout();
                                                break;
                                            case 5:
                                                //cria o objeto do grafico
                                                var chart = new Ext.Panel({
                                                    header:false,
                                                    width: '100%',
                                                    height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                                                    layout: 'anchor',
                                                    frame:false,
                                                    items: {
                                                        xtype: 'stackedbarchart',
                                                        store: store,
                                                        yField: 'category_name',
                                                        series:[{
                                                            xField:'count_jan',
                                                            displayName:'Janeiro'
                                                        },{
                                                            xField:'count_fev',
                                                            displayName:'Fevereiro'
                                                        },{
                                                            xField:'count_mar',
                                                            displayName:'Março'
                                                        },{
                                                            xField:'count_abr',
                                                            displayName:'Abril'
                                                        },{
                                                            xField:'count_mai',
                                                            displayName:'Maio'
                                                        },{
                                                            xField:'count_jun',
                                                            displayName:'Junho'
                                                        },{
                                                            xField:'count_jul',
                                                            displayName:'Julho'
                                                        },{
                                                            xField:'count_ago',
                                                            displayName:'Agosto'
                                                        },{
                                                            xField:'count_set',
                                                            displayName:'Setembro'
                                                        },{
                                                            xField:'count_out',
                                                            displayName:'Outubro'
                                                        },{
                                                            xField:'count_nov',
                                                            displayName:'Novembro'
                                                        },{
                                                            xField:'count_dez',
                                                            displayName:'Dezembro'
                                                        }],
                                                        extraStyle:{
                                                            legend:{
                                                                display: 'bottom',
                                                                padding: 5,
                                                                font:{
                                                                    family: 'Tahoma',
                                                                    size: 13
                                                                }
                                                            }
                                                        }
                                                    }
                                                });

                                                //renderiza o gráfico no painel central
                                                crc.removeAll();
                                                crc.doLayout();
                                                crc.add(chart);
                                                crc.doLayout();
                                                break;
                                            case 6:
                                                //cria o combobx de seleção do primeiro mês
                                                var cbxMonth1 = new Ext.form.ComboBox({
                                                    width:'100%',
                                                    typeAhead: true,
                                                    triggerAction: 'all',
                                                    emptyText: 'selecione 1o mês...',
                                                    id:'monthSelect1',
                                                    hiddenName:'monthSelect1ID',
                                                    mode:'local',
                                                    iconCls: 'no-icon',
                                                    store:store,
                                                    valuefield:'calendar_month',
                                                    displayField: 'month_name',
                                                    listeners:{
                                                        'select':function(combo,record,index){
                                                            month1Id = record.data.calendar_month;
                                                            RefreshMonthCompareGraph();
                                                        }
                                                    }
                                                });
                                                //cria o combobx de seleção do primeiro mês
                                                var cbxMonth2 = new Ext.form.ComboBox({
                                                    width:'100%',
                                                    typeAhead: true,
                                                    triggerAction: 'all',
                                                    emptyText: 'selecione 2o mês...',
                                                    id:'monthSelect2',
                                                    hiddenName:'monthSelect2ID',
                                                    mode:'local',
                                                    iconCls: 'no-icon',
                                                    store:store,
                                                    valuefield:'calendar_month',
                                                    displayField: 'month_name',
                                                    listeners:{
                                                        'select':function(combo,record,index){
                                                            month2Id = record.data.calendar_month;
                                                            RefreshMonthCompareGraph();
                                                        }
                                                    }
                                                });
                                                //atualiza o menu lateral com o novo combobx
                                                optionPanel.add(cbxMonth1);
                                                optionPanel.add(cbxMonth2);
                                                optionPanel.doLayout();
                                                break;
                                            default:
                                                break;
                                        }
                                    }
                                });
                            }
                        }
                    });
                    wrc.add(cbx);
                    wrc.doLayout();
                }


                function RefreshMonthCompareGraph(){
                    if(month1Id>0 && month2Id>0){
                        //carrega os dados do gráfico
                        var monthStore = new Ext.data.Store({
                            proxy: new Ext.data.HttpProxy({url: 'ajax/graph_month_compare.php?month_id1='+month1Id+'&month_id2='+month2Id}),
                            reader: new Ext.data.JsonReader({
                                root:'data',
                                fields:['calendar_day','crime_cnt1','crime_cnt2']
                            })
                        });
                        monthStore.load();

                        //cria o objeto do grafico
                        var chart = new Ext.Panel({
                            header:false,
                            width: '100%',
                            height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4,
                            layout: 'anchor',
                            frame:false,
                            items: {
                                xtype: 'columnchart',
                                store: monthStore,
                                xField: 'calendar_day',
                                series: [{
                                    type: 'column',
                                    yField: 'crime_cnt1',
                                    style: {
                                        image:'bar.gif',
                                        mode: 'stretch',
                                        color:0x99BBE8
                                    }
                                },{
                                    type: 'column',
                                    yField: 'crime_cnt2',
                                    style: {
                                        image:'bar.gif',
                                        mode: 'stretch',
                                        color:0x99fff8
                                    }
                                }]
                            }
                        });


                        //renderiza o gráfico no painel central
                        crc.removeAll();
                        crc.doLayout();
                        crc.add(chart);
                        crc.doLayout();
                    }
                }



                //Evento onClick para o botão btnMapRadious
                function btnMapRadiousClick(){
                    wrc.removeAll();
                    crc.removeAll();

                    var menu = new Ext.form.FormPanel({
                        width:'100%',
                        items:[{
                            id:'txtRaio',
                            xtype:'textfield',
                            width:'100%',
                            fieldLabel:'Raio(km)',
                            hideLabel:false,
                            value: '1'
                        },{
                            id:'txtQtdeSeg',
                            xtype:'textfield',
                            width:'100%',
                            //height:100,
                            fieldLabel:'N. Seg.',
                            hideLabel:false,
                            value: '40'
                        }]
                    });

                    wrc.add(menu);
                    wrc.doLayout();

                    //cria o painel onde irá ficar o gmaps e o gráfico
                    var mappanel = new Ext.Panel({
                        width: '100%',
                        height: wrc.getInnerHeight() + wrc.getFrameHeight(),
                        layout:'border',
                        items:[{
                            //container para o gmaps
                            id:'map_area',
                            xtype: 'gmappanel',
                            width:'50%',
                            region: 'west',
                            zoomLevel: 14,
                            gmapType: 'map',
                            mapConfOpts: ['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging'],
                            mapControls: ['GSmallMapControl','GMapTypeControl','NonExistantControl'],
                            setCenter: {
                                lat: -22.9062436,
                                lng:-47.0616158
                                //geoCodeAddr: 'Campinas',//'4 Yawkey Way, Boston, MA, 02215-3409, USA',
                                //marker: {title: 'Fenway Park'}
                            },
                            mapEvents:{
                                'click':function(overlay, latlng){
                                    clearMap();
                                    if(latlng != null){
                                        draw(latlng);
                                        map.panTo(latlng);
                                        address = latlng;
                                        geocoder.getLocations(latlng, showAddress);
                                    }
                                }
                            }
                        },{
                            //container para o gráfico
                            id:'graph_container',
                            header:false,
                            region: 'center',
                            width: '50%',
                            height: wrc.getInnerHeight() + wrc.getFrameHeight() - 4
                        },{
                            //container para o endereço
                            id:'footer_container',
                            header:false,
                            region: 'south',
                            width: '100%',
                            height: 100
                        }]
                    });
                    
                    crc.removeAll();
                    crc.add(mappanel);
                    crc.doLayout();

                    initializeRadiousVariables();
                }

            function showAddress(response){
                if (!response || response.Status.code != 200) {
                    alert("Status Code:" + response.Status.code);
                } else {
                    place = response.Placemark[0];
                    point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
                    marker = new GMarker(point);
                    map.addOverlay(marker);
                    Ext.get('footer_container').update(
                            '<table>' +
                                '<tr>' +
                                    '<td>Endreço</td>' +
                                    '<td>' + place.AddressDetails.Country.AdministrativeArea.Locality.DependentLocality.Thoroughfare.ThoroughfareName + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Bairro</td>' +
                                    '<td>' + place.AddressDetails.Country.AdministrativeArea.Locality.DependentLocality.DependentLocalityName + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Latitude</td>' +
                                    '<td>' + place.Point.coordinates[1] + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Longitude</td>' +
                                    '<td>' + place.Point.coordinates[0]+  '</td>' +
                                '</tr>' +
                            '</table>'
                    );
                }
            }
                //################# FUNÇÕES DE RAIO PARA O GMAPS ###############


                function initializeRadiousVariables(){
                    map = Ext.getCmp('map_area').getMap();
                    geocoder = new GClientGeocoder();
                }

                function fit(){
                    map.panTo(bounds.getCenter());
                    map.setZoom(map.getBoundsZoomLevel(bounds));
                }
                
                function clearMap(){
                    map.clearOverlays();
                    Ext.get('footer_container').update('');
                }

                //calling circle drawing function
                function draw(pnt){
                    map.clearOverlays();
                    bounds = new GLatLngBounds();
                    var givenRad = 0.1 //document.getElementById("radius").value*1;
                    var givenQuality = 40 //document.getElementById("seqments").value*1;

                    var txtRaio = Ext.get('txtRaio').getValue();
                    var txtQtdeSeg = Ext.get('txtQtdeSeg').getValue();

                    if (txtRaio != undefined) {
                        givenRad = txtRaio;
                    }
                    if (txtQtdeSeg != undefined){
                        givenQuality = txtQtdeSeg;
                    }

                    var centre = pnt || map.getCenter()
                    drawCircle(centre, givenRad, givenQuality);
                    fit();
                }

                function drawCircle(center, radius, nodes, liColor, liWidth, liOpa, fillColor, fillOpa) {
                    // Esa 2006
                    //calculating km/degree
                    var latConv = center.distanceFrom(new GLatLng(center.lat()+0.1, center.lng()))/100;
                    var lngConv = center.distanceFrom(new GLatLng(center.lat(), center.lng()+0.1))/100;

                    //Loop
                    var points = [];
                    var step = parseInt(360/nodes)||10;
                    for(var i=0; i<=360; i+=step) {
                        var pint = new GLatLng(center.lat() + (radius/latConv * Math.cos(i * Math.PI/180)), center.lng() + (radius/lngConv * Math.sin(i * Math.PI/180)));
                        points.push(pint);
                        bounds.extend(pint); //this is for fit function
                    }
                    points.push(points[0]); // Closes the circle, thanks Martin
                    fillColor = fillColor||liColor||"#0055ff";
                    liWidth = liWidth||2;
                    var poly = new GPolygon(points,liColor,liWidth,liOpa,fillColor,fillOpa);
                    map.addOverlay(poly);

                    var polygon;
                    polygon = 'POLYGON((';
                    for(var i=0; i<points.length; i++){
                        polygon += points[i].lat() + ' ' + points[i].lng();
                        if(i<points.length-1){
                            polygon +=',';
                        }
                    }
                    polygon += '))';

                    requestPoints(polygon);

                }

            function requestPoints(polygonDef){
                $.ajax({
                    url: "ajax/retrieve_poly_points.php",
                    type:"POST",
                    data: 'polygonDef=' + polygonDef,
                    dataType: 'json',
                    processData: false,
                    success: function(response){
                        $("#retrieved_points").html(response);
                        load_graph(response);
                    }
                });
            }

            function load_graph(data){
                var store = new Ext.data.JsonStore(data);

                var graph = Ext.getCmp("graph_container"); //.update("");

                var panel = new Ext.Panel({
                    title: 'No. Ocorrências por mês por categoria',
                    //renderTo: graph,
                    width: '100%',
                    height: graph.getInnerHeight(),
                    layout: 'anchor',
                    frame:false,
                    
                    items: {
                        xtype: 'linechart',
                        store: store,
                        xField: 'month_name',
                        tipRenderer : function(chart, record, index, series){
                            switch(series.yField){
                                case 'roubo_imovel':
                                    return Ext.util.Format.number(record.data.roubo_imovel, '0') + ' ocorrência(s) no dia ' + record.data.month_name;
                                    break;
                                case 'roubo_movel':
                                    return Ext.util.Format.number(record.data.roubo_movel, '0') + ' ocorrência(s) no dia ' + record.data.month_name;
                                    break;
                                case 'roubo_obj_pessoal':
                                    return Ext.util.Format.number(record.data.roubo_obj_pessoal, '0') + ' ocorrência(s) no dia ' + record.data.month_name;
                                    break;
                                case 'roubo_comercio':
                                    return Ext.util.Format.number(record.data.roubo_comercio, '0') + ' ocorrência(s) no dia ' + record.data.month_name;
                                    break;
                                case 'roubo_automovel':
                                    return Ext.util.Format.number(record.data.roubo_automovel, '0') + ' ocorrência(s) no dia ' + record.data.month_name;
                                    break;
                                case 'roubo_outros':
                                    return Ext.util.Format.number(record.data.roubo_outros, '0') + ' ocorrência(s) no dia ' + record.data.month_name;
                                    break;
                                default:
                                    return Ext.util.Format.number(record.data.total, '0') + ' ocorrência(s) no dia ' + record.data.month_name;
                                    break;
                            }
                        },
                        series: [{
                                type: 'line',
                                displayName: 'Roubo - Móveis',
                                yField: 'roubo_imovel',
                                style: {
                                    color:0x99BBE8
                                },
                                allowBlank:false
                            },{
                                type:'line',
                                displayName: 'Roubo - Imóveis',
                                yField: 'roubo_movel',
                                style: {
                                    color: 0x15428B
                                },
                                allowBlank:false
                            },{
                                type:'line',
                                displayName: 'Roubo - Objetos Pessoais',
                                yField: 'roubo_obj_pessoal',
                                style: {
                                    color: 0x154ddB
                                },
                                allowBlank:false
                            },{
                                type:'line',
                                displayName: 'Total',
                                yField: 'total',
                                style: {
                                    color: 0x1ff28B
                                },
                                allowBlank:false
                       }],
                       extraStyle: {
                           legend: {
                               display: 'bottom'
                           }
                       }
                    }
                });

                graph.removeAll();
                graph.add(panel);
                graph.doLayout();
            }
                
            btnHomeClick();





            });
        </script>
    </head>
    <body>
        <input type="hidden" value="<?php echo $admin_flag; ?>" id="admin_flag"></input>
    </body>
</html>