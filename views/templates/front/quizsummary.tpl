{extends file='layouts/layout-full-width.tpl'}

{block name='content'}
    <section id="quizbhp">
        <form method="POST" action="{Context::getContext()->link->getModuleLink('quizbhp', 'quizsummary')}">
            <div class="form-group">
                <label for="name">Imię</label>
                <input type="text" required class="form-control" id="name" name="name" placeholder="Imię">
            </div>
            <div class="form-group">
                <label for="surname">Nazwisko</label>
                <input type="text" required class="form-control" id="surname" name="surname" placeholder="Nazwisko">
            </div>
            <div class="form-group">
                <label for="placeOfBirth">Miejsce urodzenia</label>
                <input type="text" required class="form-control" id="placeOfBirth" name="placeOfBirth" placeholder="Miejsce urodzenia">
            </div>
            <div class="form-group">
                <label for="placeOfBirth">Data urodzenia</label>
                <input type="date" required class="form-control" id="dateOfBirth" name="dateOfBirth">
            </div>
            <input type="hidden" name='quizId' value="{Tools::getValue('quizId')}"/>
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
