<?php
/**
 * Fonctions utiles au plugin Liste en case
 *
 * @plugin     Liste en case
 * @copyright  2014
 * @author     Vertige (Didier)
 * @licence    GNU/GPL
 * @package    SPIP\Newsliste\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Surcharge pour prendre en compte un tableau passé via post
 */
/**
 * Traiter les champs postes
 */
function formulaires_newsletter_send_traiter($id_newsletter,$mode_test=false){

  $res = array('message_erreur'=>"lapin compris");

  if (_request('envoi_test')){
    $email = _request('email_test');
    // recuperer l'abonne si il existe avec cet email
    $subscriber = charger_fonction('subscriber','newsletter');
    $dest = $subscriber($email);

    // si abonne inconnu, on simule (pour les tests)
    if (!$dest)
      $dest = array(
        'email' => $email,
        'nom' => $GLOBALS['visiteur_session']['nom'],
        'lang' => $GLOBALS['visiteur_session']['lang'],
        'status' => 'on',
        'url_unsubscribe' => url_absolue(_DIR_RACINE . "unsubscribe"),
      );

    // ok, maintenant on prepare un envoi
    $send = charger_fonction("send","newsletter");
    $res = $send($dest, $id_newsletter, array('test'=>$mode_test?true:false));

    if (!$res)
      $res = array('message_ok'=>_T($mode_test?'newsletter:info_test_envoye':'newsletter:info_envoi_unique_reussi',array('email'=>$email)));
    else
      $res = array('message_erreur'=>$res);
  }
  elseif (_request('envoi')){

    $listes = array();
    if ($liste = _request('liste')){
      $listes = $liste;
    }

    $bulkstart = charger_fonction("bulkstart","newsletter");

    if ($id_mailshot = $bulkstart($id_newsletter, $listes)){
      $total = sql_getfetsel('total','spip_mailshots','id_mailshot='.intval($id_mailshot));
      $res = array('message_ok'=>singulier_ou_pluriel($total,'mailshot:info_envoi_programme_1_destinataire','mailshot:info_envoi_programme_nb_destinataires'));
      set_request('liste','');
    }
    else
      $res = array('message_erreur'=>'erreur');
  }

  return $res;
}

/**
 * Renvoyer le titre d'une liste en fonction de sont id
 * @param  string $id_liste Identifiant de la liste
 * @return string           Titre de la liste
 */
function trouver_nom_liste($id_liste) {
  // On récupère toute les listes:
  $lists = charger_fonction('lists','newsletter');
  $listes = $lists(array('status'=>'open'));

  return $listes[$id_liste]['titre'];
}