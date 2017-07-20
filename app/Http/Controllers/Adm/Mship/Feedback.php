<?php

namespace App\Http\Controllers\Adm\Mship;

use App\Http\Requests\Mship\Feedback\UpdateFeedbackFormRequest;
use App\Models\Contact;
use App\Models\Mship\Feedback\Feedback as FeedbackModel;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use App\Models\Mship\Feedback\Question\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class Feedback extends \App\Http\Controllers\Adm\AdmController
{
    public function getNewForm()
    {
        $question_types = Type::all();
        $new_question = new Question();

        return $this->viewMake('adm.mship.feedback.new')
            ->with('question_types', $question_types)
            ->with('new_question', $new_question);
    }

    public function postNewForm(UpdateFeedbackFormRequest $request)
    {
        $new_ident   = isset($_POST['ident'])   ? $_POST['ident']   : null;
        $new_name    = isset($_POST['name'])    ? $_POST['name']    : null;
        $new_contact = isset($_POST['contact']) ? $_POST['contact'] : null;
        if ($new_ident == null) {
            return Redirect::back()
                ->withInput($request->input())
                ->withError('Form \'ident\' not specified');
        }
        if ($new_name == null) {
            return Redirect::back()
                ->withInput($request->input())
                ->withError('Form \'name\' not specified');
        }
        if (Form::whereSlug($new_ident)->exists()) {
            return Redirect::back()
                ->withInput($request->input())
                ->withError('New form identifier \''.$new_ident.'\' already exists');
        }

        $form = $this->makeNewForm($new_ident, $new_name, $new_contact);

        $this->makeUserCidQuestion($form, [
            'name' => 'CID of the member you are leaving feedback for.',
            'slug' => 'usercid',
            'required' => true,
            'type' => 'userlookup']);

        return $this->postConfigure($form, $request);
    }

    public function getConfigure(Form $form)
    {
        $question_types = Type::all();
        $current_questions = $form->questions()->orderBy('sequence')->notPermanent()->get();
        $new_question = new Question();

        return $this->viewMake('adm.mship.feedback.settings')
                    ->with('question_types', $question_types)
                    ->with('current_questions', $current_questions)
                    ->with('new_question', $new_question)
                    ->with('form', $form);
    }

    public function postConfigure(Form $form, UpdateFeedbackFormRequest $request)
    {
        $in_use_question_ids = [];

        $all_current_questions = $form->questions;
        $permanent_questions = $all_current_questions->filter(function ($question, $key) {
            if ($question->permanent) {
                return true;
            }

            return false;
        });
        foreach ($permanent_questions as $question) {
            $in_use_question_ids[] = ['id', '!=', $question->id];
        }

        $i = $permanent_questions->count() + 1;
        foreach (array_values($request->input('question')) as $question) {
            if (isset($question['exists'])) {
                // The question exisits already. Lets see if it is appropriate to create a new question, or update.
                $exisiting_question = Question::find($question['exists']);
                if ($exisiting_question->question != $question['name']) {
                    // Make a new question
                    $exisiting_question->delete();
                    $in_use_question_ids[] = ['id', '!=', $this->makeNewQuestion($form, $question, $i)];
                    $i++;
                    continue;
                }

                // We will update it instead
                $exisiting_question->required = $question['required'];
                $exisiting_question->slug = $question['slug'].$i;
                $exisiting_question->sequence = $i;
                if (isset($question['options']['values'])) {
                    $question['options']['values'] = explode(',', $question['options']['values']);
                }
                if (isset($question['options'])) {
                    $exisiting_question->options = $question['options'];
                } else {
                    $exisiting_question->options = null;
                }

                $exisiting_question->required = $question['required'];
                $exisiting_question->save();
                $in_use_question_ids[] = ['id', '!=', $exisiting_question->id];
                $i++;
                continue;
            } else {
                // Make a new question
                $in_use_question_ids[] = ['id', '!=', $this->makeNewQuestion($form, $question, $i)];
                $i++;
                continue;
            }
        }

        //Check if we have lost any questions along the way, and delete them
        $form->questions()->where($in_use_question_ids)->delete();

        return Redirect::back()
                      ->withSuccess('Updated!');
    }

    public function postEnableForm(Form $form) {
        $form->enabled = true;
        $form->save();
        return Redirect::back()
            ->withSuccess('Updated!');
    }

    public function postDisableForm(Form $form) {
        $form->enabled = false;
        $form->save();
        return Redirect::back()
            ->withSuccess('Updated!');
    }

    public function makeNewQuestion($form, $question, $sequence)
    {
        $type = Type::where('name', $question['type'])->first();
        $new_question = new Question();
        $new_question->question = $question['name'];
        $new_question->slug = $question['slug'].$sequence;
        $new_question->type_id = $type->id;
        $new_question->form_id = $form->id;
        if (isset($question['options']['values']) && $question['options']['values'] != '') {
            $question['options']['values'] = explode(',', $question['options']['values']);
        }
        if (isset($question['options'])) {
            $new_question->options = $question['options'];
        }
        $new_question->required = $question['required'];
        $new_question->sequence = $sequence;
        $new_question->permanent = false;
        $new_question->save();

        return $new_question->id;
    }

    public function makeUserCidQuestion($form, $question)
    {
        $type = Type::where('name', 'userlookup')->first();
        $new_question = new Question();
        $new_question->question = $question['name'];
        $new_question->slug = $question['slug'];
        $new_question->type_id = $type->id;
        $new_question->form_id = $form->id;
        if (isset($question['options']['values']) && $question['options']['values'] != '') {
            $question['options']['values'] = explode(',', $question['options']['values']);
        }
        if (isset($question['options'])) {
            $new_question->options = $question['options'];
        }
        $new_question->required = $question['required'];
        $new_question->sequence = 1;
        $new_question->permanent = true;
        $new_question->save();

        return $new_question->id;
    }

    public function makeNewForm($ident, $name, $contact)
    {
        $new_form = new Form();
        $new_form->slug = $ident;
        $new_form->name = $name;
        if ($contact != null && $contact != '') {
            $contact_model = Contact::whereEmail($contact);
            if ($contact_model->exists()) {
                $new_form->contact_id = $contact_model->first()->id;
            } else {
                $new_contact = new Contact();
                $contact_prefix = ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', explode('@', $contact)[0]));
                $contact_key = strtoupper(preg_replace('/[\s]+/', '_', $contact_prefix));
                $new_contact->key = $contact_key;
                $new_contact->name = $contact_prefix;
                $new_contact->email = $contact;
                $new_contact->save();
                $new_form->contact_id = $new_contact->id;
            }
        }
        $new_form->enabled = false;
        $new_form->save();

        return $new_form;
    }

    public function getAllFeedback()
    {
        if (!$this->account->hasChildPermission('adm/mship/feedback/list')) {
            abort(401, 'Unauthorized action.');
        }

        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->get();

        return $this->viewMake('adm.mship.feedback.list')
                    ->with('feedback', $feedback);
    }

    public function getFormFeedback($slug)
    {
        if (!$this->account->hasPermission('adm/mship/feedback/list/*')) {
            abort(401, 'Unauthorized action.');
        }

        $form = Form::whereSlug($slug)->firstOrFail();
        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->whereFormId($form->id)->get();

        return $this->viewMake('adm.mship.feedback.list')
                    ->with('feedback', $feedback);
    }

    public function getViewFeedback(FeedbackModel $feedback)
    {
        if ($this->account->hasChildPermission('adm/mship/feedback/list')) {
            return $this->viewMake('adm.mship.feedback.view')
                    ->with('feedback', $feedback);
        }
        if ($this->account->hasChildPermission('adm/mship/feedback/list/atc') && $feedback->isATC() == true) {
            return $this->viewMake('adm.mship.feedback.view')
                      ->with('feedback', $feedback);
        }
        if ($this->account->hasChildPermission('adm/mship/feedback/list/pilot') && $feedback->isATC() == false) {
            return $this->viewMake('adm.mship.feedback.view')
                    ->with('feedback', $feedback);
        }
        abort(401, 'Unauthorized action.');
    }

    public function postActioned(FeedbackModel $feedback, Request $request)
    {
        $conditions = [];
        $conditions[] = $this->account->hasChildPermission('adm/mship/feedback/list');
        $conditions[] = ($this->account->hasChildPermission('adm/mship/feedback/list/atc') && $feedback->isATC() == true);
        $conditions[] = ($this->account->hasChildPermission('adm/mship/feedback/list/pilot') && $feedback->isATC() == false);

        foreach ($conditions as $condition) {
            if ($condition) {
                $feedback->markActioned(\Auth::user(), $request->input('comment'));

                return Redirect::back()
                              ->withSuccess('Feedback marked as actioned!');
            }
        }
        abort(401, 'Unauthorized action.');
    }

    public function getUnActioned(FeedbackModel $feedback)
    {
        $conditions = [];
        $conditions[] = $this->account->hasChildPermission('adm/mship/feedback/list');
        $conditions[] = ($this->account->hasChildPermission('adm/mship/feedback/list/atc') && $feedback->isATC() == true);
        $conditions[] = ($this->account->hasChildPermission('adm/mship/feedback/list/pilot') && $feedback->isATC() == false);

        foreach ($conditions as $condition) {
            if ($condition) {
                $feedback->markUnActioned();

                return Redirect::back()
                              ->withSuccess('Feedback unmarked as actioned!');
            }
        }
        abort(401, 'Unauthorized action.');
    }
}
