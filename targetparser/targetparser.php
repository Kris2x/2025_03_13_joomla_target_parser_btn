<?php
defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

class PlgEditorsXtdTargetParser extends CMSPlugin
{
  protected $autoloadLanguage = true;

  public function onDisplay($editor): CMSObject
  {
    // Pobierz dokument
    $doc = Factory::getDocument();

    // Definicja przycisku
    $button = new CMSObject;
    $button->modal = false;
    $button->name = 'targetparser'; // Unikalna nazwa
    $button->text = 'Target Parser'; // Wyświetlana nazwa
    $button->icon = 'star'; // Ikona zgodna z JCE
    $button->link = '#';
    $button->class = 'btn btn-secondary'; // Styl zgodny z JCE

    // JavaScript po kliknięciu
    $button->onclick = "jceInsertCustomLogic('" . $editor . "'); return false;";

    // Dodaj skrypt JavaScript
    $doc->addScriptDeclaration("
            function jceInsertCustomLogic(editor) {
                try {
                    var content, newContent;

                    if (typeof WFEditor !== 'undefined') {
                        // Dla JCE
                        content = WFEditor.getContent(editor);
                    } else if (typeof Joomla.editors.instances[editor] !== 'undefined') {
                        // Dla innych edytorów (np. TinyMCE)
                        content = Joomla.editors.instances[editor].getValue();
                    } else {
                        alert('Edytor nie jest obsługiwany!');
                        return;
                    }

                    // Parsowanie HTML i modyfikacja znaczników <a>
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(content, 'text/html');
                    var links = doc.getElementsByTagName('a');
                    var updatedCount = 0;

                    for (var i = 0; i < links.length; i++) {
                        var currentTarget = links[i].getAttribute('target');
                        if (currentTarget !== '_blank') {
                            links[i].setAttribute('target', '_blank');
                            updatedCount++;
                        }
                    }

                    // Jeśli nie zaktualizowano żadnych linków, zakończ z komunikatem
                    if (updatedCount === 0) {
                        alert('Nie znaleziono linków do zaktualizowania - wszystkie już mają target=\"_blank\".');
                        return;
                    }

                    // Konwersja zmodyfikowanego HTML z powrotem na string
                    newContent = doc.documentElement.outerHTML;
                    newContent = newContent.replace(/<html><head>.*?<\/head><body>/, '').replace(/<\/body><\/html>/, '');

                    // Wstaw zmodyfikowaną zawartość z powrotem do edytora
                    if (typeof WFEditor !== 'undefined') {
                        WFEditor.setContent(editor, newContent);
                    } else {
                        Joomla.editors.instances[editor].setValue(newContent);
                    }

                    alert('Zaktualizowano target=\"_blank\" dla ' + updatedCount + ' pozycji!');
                } catch (e) {
                    console.error('Błąd w obsłudze edytora: ' + e.message);
                    alert('Wystąpił błąd: ' + e.message);
                }
            }
        ");

    return $button;
  }
}