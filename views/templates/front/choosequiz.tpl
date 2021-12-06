{extends file='layouts/layout-full-width.tpl'}

{block name='content'}
    <section id="quizbhp">
        <header class="page-header">
            <h1>Wybierz test</h1>
        </header>
        {foreach $quizList as $quiz}
            <div><a href="{Context::getContext()->link->getModuleLink('quizbhp', 'quiz', ['quizId' => $quiz['id_quiz_bhp']])}"><button class="btn btn-secondary">{$quiz['name']}</button></a></div>
        {/foreach}
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
