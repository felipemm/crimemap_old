<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <title>Statísticas dentro de uma região</title>

        <!-- ** CSS ** -->
        <!-- base library -->
        <link rel="stylesheet" type="text/css" href="http://cdn.sencha.io/ext-3.3.0/resources/css/ext-all.css" />
        <!-- overrides to base library -->

        <!-- ** Javascript ** -->
        <!-- ExtJS library: base/adapter -->
        <script type="text/javascript" src="http://cdn.sencha.io/ext-3.3.0/adapter/ext/ext-base.js"></script>
        <!-- ExtJS library: all widgets -->
        <script type="text/javascript" src="http://cdn.sencha.io/ext-3.3.0/ext-all.js"></script>
        <!-- overrides to library -->
        <!-- extensions -->
        <!-- page specific -->
        <script type="text/javascript">
            // Path to the blank image should point to a valid location on your server
            Ext.BLANK_IMAGE_URL = 'http://cdn.sencha.io/ext-3.3.0resources/images/default/s.gif';

            Ext.onReady(function(){
                Ext.QuickTips.init();

                var msg = function(title, msg) {
                    Ext.Msg.show({
                        title: title,
                        msg: msg,
                        minWidth: 200,
                        modal: true,
                        icon: Ext.Msg.INFO,
                        buttons: Ext.Msg.OK
                    });
                };

                var loginForm = new Ext.form.FormPanel({
                    frame:true,
                    width:260,
                    labelWidth:60,
                    defaults: {
                        width: 165
                    },
                    items: [
                        new Ext.form.TextField({
                            id:"username",
                            fieldLabel:"Usuário",
                            allowBlank:false,
                            blankText:"Preencha o nome do usuário"
                        }),
                        new Ext.form.TextField({
                            id:"password",
                            fieldLabel:"Senha",
                            inputType: 'password',
                            allowBlank:false,
                            blankText:"Coloque a sua senha"
                        })
                    ],
                    buttons: [{
                        text: 'Entrar',
                        handler: function(){
                            if(loginForm.getForm().isValid()){
                                loginForm.getForm().submit({
                                    url: 'ajax/check_login.php',
                                    waitMsg: 'Processando',
                                    success: function(loginForm, resp){
                                        Ext.MessageBox.alert('Sucesso!', resp.result.msg, function(){location.href = 'index.php';});
                                    },
                                    failure:function(loginForm, resp){
                                        Ext.MessageBox.alert('Erro!', resp.result.msg);
                                    }
                                });
                            }
                        }
                    },{
                        text:'Novo Usuário',
                        handler:function(){
                            var cadForm = new Ext.FormPanel({
                                id:'cadForm',
                                frame:true,
                                width:300,
                                height:150,
                                labelWidth:100,
                                defaults: {
                                    width: 165
                                },
                                items: [
                                    new Ext.form.TextField({
                                        id:"cad_username",
                                        fieldLabel:"Usuário",
                                        allowBlank:false,
                                        blankText:"Preencha o nome do usuário"
                                    }),
                                    new Ext.form.TextField({
                                        id:"cad_password",
                                        fieldLabel:"Senha",
                                        inputType: 'password',
                                        allowBlank:false,
                                        blankText:"Coloque a sua senha"
                                    }),
                                    new Ext.form.TextField({
                                        id:"cad_real_name",
                                        fieldLabel:"Nome Completo",
                                        allowBlank:false,
                                        blankText:"Coloque a seu nome completo"
                                    }),
                                    new Ext.form.TextField({
                                        id:"cad_email",
                                        fieldLabel:"e-Mail",
                                        allowBlank:false,
                                        blankText:"Coloque a seu e-mail de contato"
                                    })
                                ],
                                buttons: [{
                                    text: 'Cadastrar',
                                    handler: function(){
                                        var cadForm = Ext.getCmp('cadForm');
                                        if(cadForm.getForm().isValid()){
                                            cadForm.getForm().submit({
                                                url: 'ajax/cad_login.php',
                                                waitMsg: 'Processando...',
                                                success: function(form, resp){
                                                    msg('Sucesso!', resp.result.msg);
                                                    win.close();
                                                },
                                                failure:function(form, resp){
                                                    msg('Erro!', resp.result.msg);
                                                }
                                            });
                                        }
                                    }
                                },{
                                    text:'Cancelar',
                                    handler:function(){
                                        win.close();
                                    }
                                }]
                            })
                            var win = new Ext.Window({
                                id:'cadWindow',
                                title:'Cadastrar novo usuário',
                                layout:'fit',
                                closable:false,
                                draggable:false,
                                modal:true,
                                items:[cadForm]
                            });
                            win.show();
                        }
                    }]
                });

                var loginWindow = new Ext.Window({
                    title: 'CrimeMap - Campinas',
                    layout: 'fit',
                    height: 140,
                    width: 260,
                    closable: false,
                    resizable: false,
                    draggable: false,
                    modal:true,
                    items: [loginForm]
                });

                loginWindow.show();
            });
        </script>
    </head>
    <body></body>
</html>