<?php
// This file is part of Moodle - http://moodle.org/
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Import Microsoft Word file form.
 *
 * @package    booktool
 * @subpackage wordimport
 * @copyright  2015 Eoin Campbell
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* This file contains code based on mod/book/tool/importhtml/import_form.php
 * (copyright 2004-2011 Petr Skoda) from Moodle 2.4. */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . DIRECTORY_SEPARATOR . 'formslib.php');

class booktool_wordimport_add_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('header', 'generalfile', get_string('import'));
        if (method_exists($mform, 'setExpanded')) {     // Moodle 2.5.
            $mform->setExpanded('generalfile');
        }

        $mform->addElement('filemanager', 'importfile',
                           get_string('wordfile', 'booktool_wordimport'), null,
                           array('subdirs' => 0,
                                 'accepted_types' => array('.docx')));

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitfilebutton',
                                                get_string('import'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('header', 'options', get_string('optionsheader',
                                                           'resource'));
        if (method_exists($mform, 'setExpanded')) {     // Moodle 2.5.
            $mform->setExpanded('options');
        }

        $mform->addElement('checkbox', 'chaptersasbooks', '',
                           get_string('chaptersasbooks', 'booktool_wordimport'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'chapterid');
        $mform->setType('chapterid', PARAM_INT);

        $this->set_data($data);
    }

    public function validation($data, $files) {
        global $USER;

        if ($errors = parent::validation($data, $files)) {
            return $errors;
        }

        if (isset($data['submitfilebutton'])) {
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();

            if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft',
                                              $data['importfile'], 'id', false)) {
                $errors['importfile'] = get_string('required');
                return $errors;
            } else {
                $file = reset($files);
                $mimetype = $file->get_mimetype();
                if ($mimetype != 'application/epub+zip' and
                    $mimetype != 'application/zip' and
                    $mimetype != 'document/unknown' and
                    $mimetype != null) {
                    $errors['importfile'] = get_string('invalidfiletype',
                                                       'error',
                                                       $file->get_filename());
                    $fs->delete_area_files($usercontext->id, 'user', 'draft',
                                           $data['importfile']);
                } /* else {
                    if (!$chapterfiles = toolbook_wordimport_get_chapter_files($file, $usercontext)) {
                        $errors['importfile'] = get_string('errornochapters',
                                                           'booktool_importhtml');
                    }
                } */
            }
        }

        return $errors;
    }
}
