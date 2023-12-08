<?php

/*
 *  Controle de acesso para sistemas B10 Web
 *  FALSE = deny access/exibtion.
 *  TRUE = allow access/exibtion.
 */

/*
 *  Regras de acesso.
 *  Ex.:
 *  $config['acl']['id']['controller']['method']['user_stat'] = TRUE;
 */

$config['acl'][0]['*']['*']['*']= FALSE;
$config['acl'][1]['home']['*']['*']= TRUE;
$config['acl'][2]['home']['sem_confirmacao']['*']= FALSE;
$config['acl'][3]['home']['sem_confirmacao']['inner']= TRUE;
$config['acl'][4]['home']['sem_retorno']['*']= FALSE;
$config['acl'][5]['home']['sem_retorno']['inner']= TRUE;
$config['acl'][6]['home']['index']['*']= FALSE;
$config['acl'][7]['home']['index']['logged']= TRUE;



/*
 *  Disponibilidade de menus.
 *  Ex.:
 *  $config['menus']['principal']=array(
 *      'label'     =>  'HOME',
 *      'url'       =>  base_url('home'),
 *      'acl'       =>  array(
 *          '*'         =>  TRUE,
 *      ),
 *      'submenu'   =>  array(
 *          array(
 *              'label'     =>  'SUBMENU 1',
 *              'url'       =>  base_url('home/submenu1'),
 *              'acl'       =>  array(
 *                  '*'         =>  FALSE,
 *                  'logged'    =>  TRUE,
 *              ),
 *          ),
 *          array(
 *              'label'     =>  'SUBMENU 2',
 *              'url'       =>  base_url('home/submenu2'),
 *              'acl'       =>  array(
 *                  '*'         =>  FALSE,
 *                  'logged'    =>  TRUE,
 *              ),
 *          ),
 *      ),
 *  );
 */

$config['menus']['principal']=array(
    'id'        =>  'titulos',
    'label'     =>  'Títulos',
    'url'       =>  '*',
    'acl'       =>  array(
        '*'         =>  TRUE,
    ),
    'submenu'   =>  array(
        array(
            'id'        =>  'titulos/submenu1',
            'label'     =>  'SUBMENU 1',
            'url'       =>  base_url('home/submenu1'),
            'acl'       =>  array(
                '*'         =>  FALSE,
                'logged'    =>  TRUE,
            ),
        ),
        array(
            'id'        =>  'titulos/submenu2',
            'label'     =>  'SUBMENU 2',
            'url'       =>  base_url('home/submenu2'),
            'acl'       =>  array(
                '*'         =>  FALSE,
                'logged'    =>  TRUE,
            ),
        ),
    ),
);

/*
 *  Liberações de acesso.
 *  Constituido de 3 partes: ACLs, Menus e Permissões especiais
 *  Ex.:
 *  
 *  $config['access_set']['titulos_inclusao']['label']  = 'Inclusão de Títulos';
 * 
 *  $config['access_set']['titulos_inclusao']['acls'][]['lotes']['criar'] = TRUE;
 *  $config['access_set']['titulos_inclusao']['acls'][]['lotes']['editar'] = TRUE;
 *  $config['access_set']['titulos_inclusao']['acls'][]['lotes']['fechar'] = TRUE;
 * 
 *  $config['access_set']['titulos_inclusao']['menus'][]['titulos'] = TRUE;
 *  $config['access_set']['titulos_inclusao']['menus'][]['titulos/incluir'] = TRUE;
 *  $config['access_set']['titulos_inclusao']['menus'][]['titulos/remessas'] = TRUE;
 * 
 *  $config['access_set']['titulos_inclusao']['permissoes'][]['fechar_remessa'] = TRUE;
 */