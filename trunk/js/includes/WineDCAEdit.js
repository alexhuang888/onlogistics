/**
 *
 * @source
 * @version $Id: WineDCAEdit.js 9 2008-06-06 09:12:09Z izimobil $
 */

// validateForm() {{{

/**
 * valide les donn�es saisie
 */
validateForm = function() {
    // un site destinataire doit �tre saisie
    // ok, on poste le form
    $('FormSubmitted').value = true;
    document.forms[0].submit();
	return true;
};

// }}}
