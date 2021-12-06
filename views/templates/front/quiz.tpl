{extends file='layouts/layout-full-width.tpl'}

{block name='content'}
    <section id="quizbhp">
        <header class="page-header">
            <h1>{$quizName}</h1>
        </header>
        {if $quizResult == 'failedQuiz'}
        <div class="alert alert-danger d-print-none" role="alert">
            <div class="alert-text">
              <p>Nie zdałeś testu. Spróbuj ponownie.</p>
          </div>
        </div>
        {/if}
        {if $quizResult == 'passedQuiz'}
        <div class="alert alert-success d-print-none" role="alert">
            <div class="alert-text">
              <p>Udało Ci się zdać test</p>
          </div>
        </div>
        {/if}
        <form method="POST" action="{Context::getContext()->link->getModuleLink('quizbhp', 'quiz')}">
            {for $index=0 to count($questions) - 1}
                <div class="form-group"><label style="text-align: left; font-weight: bold;">{$index + 1}. {$questions[$index]['question']}</label>
                    {foreach $questions[$index]['answers'] as $answer}
                        <div class="form-check">
                            <input class="form-check-input" required type="radio" name="question_{$questions[$index]['id_quiz_bhp_question']}" id="answer_{$answer['id_quiz_answer']}" value="{$answer['id_quiz_answer']}">
                            <label class="form-check-label" for="answer_{$answer['id_quiz_answer']}">
                                {$answer['answer']}
                            </label>
                        </div>
                    {/foreach}
                    <input type="hidden" name='quizId' value="{Tools::getValue('quizId')}"/>
                <div>
            {/for}
            <button type="submit" class="btn btn-primary">Prześlij</button>
            <a href="{Context::getContext()->link->getModuleLink('quizbhp', 'choosequiz')}"><button type="button" class="btn btn-secondary">Wróć</button></a>
        </form>
    </section>
    <style>
    #wrapper {
        background: white !important;
    }
    #quizbhp {
        width: 50%;
        margin: auto;
    }
</style>
{/block}
