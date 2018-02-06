<?php
/**
 * Behat2 renderer for Behat report
 * @author DaSayan <glennwall@free.fr>
 */

namespace emuse\BehatHTMLFormatter\Renderer;

use Behat\Gherkin\Node\TableNode;

class Behat2Renderer implements RendererInterface {

    /**
     * Renders before an exercice.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeExercise($obj)
    {

        $print = "<div id='behat'>";

        return $print;
    }

    /**
     * Renders after an exercice.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterExercise($obj)
    {
        //--> features results
        $strFeatPassed = '';
        if(count($obj->getPassedFeatures()) > 0) {
            $strFeatPassed = ' <strong class="passed">'.count($obj->getPassedFeatures()).' success</strong>';
        }

        $strFeatFailed = '';
        $sumRes = 'passed';
        if(count($obj->getFailedFeatures()) > 0) {
            $strFeatFailed = ' <strong class="failed">'.count($obj->getFailedFeatures()).' fail</strong>';
            $sumRes = 'failed';
        }

        //--> scenarios results
        $strScePassed = '';
        if(count($obj->getPassedScenarios()) > 0) {
            $strScePassed = ' <strong class="passed">'.count($obj->getPassedScenarios()).' success</strong>';
        }

        $strScePending = '';
        if(count($obj->getPendingScenarios()) > 0) {
            $strScePending = ' <strong class="pending">'.count($obj->getPendingScenarios()).' pending</strong>';
        }

        $strSceFailed = '';
        if(count($obj->getFailedScenarios()) > 0) {
            $strSceFailed = ' <strong class="failed">'.count($obj->getFailedScenarios()).' fail</strong>';
        }

        //--> steps results
        $strStepsPassed = '';
        if(count($obj->getPassedSteps()) > 0) {
            $strStepsPassed = ' <strong class="passed">'.count($obj->getPassedSteps()).' success</strong>';
        }

        $strStepsPending = '';
        if(count($obj->getPendingSteps()) > 0) {
            $strStepsPending = ' <strong class="pending">'.count($obj->getPendingSteps()).' pending</strong>';
        }

        $strStepsSkipped = '';
        if(count($obj->getSkippedSteps()) > 0) {
            $strStepsSkipped = ' <strong class="skipped">'.count($obj->getSkippedSteps()).' skipped</strong>';
        }

        $strStepsFailed = '';
        if(count($obj->getFailedSteps()) > 0) {
            $strStepsFailed = ' <strong class="failed">'.count($obj->getFailedSteps()).' fail</strong>';
        }

        //totals
        $featTotal = (count($obj->getFailedFeatures()) + count($obj->getPassedFeatures()));
        $sceTotal = (count($obj->getFailedScenarios()) + count($obj->getPendingScenarios()) + count($obj->getPassedScenarios()));
        $stepsTotal = (count($obj->getFailedSteps()) + count($obj->getPassedSteps()) + count($obj->getSkippedSteps()) + count($obj->getPendingSteps()));

        //last run datetime
        date_default_timezone_set('America/Denver');
        $dateRun = date("D, d M y H:i:s O", time());

        //list of pending steps to display
        $strPendingList = '';
        if(count($obj->getPendingSteps()) > 0) {
            foreach($obj->getPendingSteps() as $pendingStep) {
                $strPendingList .= '
                    <li>'.$pendingStep->getKeyword().' '.htmlentities($pendingStep->getText()).'</li>';
            }
            $strPendingList = '
            <div class="pending">Pending steps :
                <ul>'.$strPendingList.'
                </ul>
            </div>';
        }

        $print = '
        <div class="summary '.$sumRes.'">
            <div class="counters">
                <p class="features">
                    '.$featTotal.' features ('.$strFeatPassed.$strFeatFailed.' )
                </p>
                <p class="scenarios">
                    '.$sceTotal.' scenarios ('.$strScePassed.$strScePending.$strSceFailed.' )
                </p>
                <p class="steps">
                    '.$stepsTotal.' steps ('.$strStepsPassed.$strStepsPending.$strStepsSkipped.$strStepsFailed.' )
                </p>
                <p class="time">
                '.$obj->getTimer().' - '.$obj->getMemory().'
                </p>
                <p class="date">
                  Last run on '.$dateRun.'
                </p>
            </div>
            <div class="switchers">
                <a href="javascript:void(0)" id="behat_show_all">[+] all</a>
                <a href="javascript:void(0)" id="behat_hide_all">[-] all</a>
            </div>
        </div> '.$strPendingList.'
    </div>'.$this->getJS();

        return $print;

    }

    /**
     * Renders before a suite.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeSuite($obj)
    {
        $print = '
        <div class="suite">Suite : '.$obj->getCurrentSuite()->getName().'</div>';

        return $print;

    }

    /**
     * Renders after a suite.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterSuite($obj)
    {
        return '';
    }

    /**
     * Renders before a feature.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeFeature($obj)
    {

        //feature head
        $print = '
        <div class="feature">
            <h2>
                <span id="feat'.$obj->getCurrentFeature()->getId().'" class="keyword"> Feature: </span>
                <span class="title">'.$obj->getCurrentFeature()->getName().'</span>
            </h2>
            <p>'.$obj->getCurrentFeature()->getDescription().'</p>
            <ul class="tags">';
        foreach($obj->getCurrentFeature()->getTags() as $tag) {
            $print .= '
                <li>@'.$tag.'</li>';
        }
        $print .= '
            </ul>';

        //TODO path is missing (?)

        return $print;
    }

    /**
     * Renders after a feature.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterFeature($obj)
    {
        //list of results
        $print = '
            <div class="featureResult '.$obj->getCurrentFeature()->getPassedClass().'">Feature has '.$obj->getCurrentFeature()->getPassedClass();

        //percent only if failed scenarios
        if($obj->getCurrentFeature()->getTotalAmountOfScenarios() > 0 && $obj->getCurrentFeature()->getPassedClass() === 'failed') {
            $print .= '
                <span>Scenarios passed : '.round($obj->getCurrentFeature()->getPercentPassed(), 2).'%,
                Scenarios failed : '.round($obj->getCurrentFeature()->getPercentFailed(), 2).'%</span>';
        }

        $print .= '
            </div>
        </div>';

        return $print;
    }

    /**
     * Renders before a scenario.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeScenario($obj)
    {
        //scenario head
        $print = '
            <div class="scenario">
                <ul class="tags">';
        foreach($obj->getCurrentScenario()->getTags() as $tag) {
            $print .= '
                    <li>@'.$tag.'</li>';
        }
        $print .= '
                </ul>';

        $print .= '
                <h3>
                    <span class="keyword">'.$obj->getCurrentScenario()->getId().' Scenario: </span>
                    <span class="title">'.$obj->getCurrentScenario()->getName().'</span>
                </h3>
                <ol>';

        //TODO path is missing

        return $print;
    }

    /**
     * Renders after a scenario.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterScenario($obj)
    {
        $print = '
                </ol>
            </div>';

        return $print;
    }

    /**
     * Renders before an outline.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeOutline($obj)
    {
        //scenario head
        $print = '
            <div class="scenario">
                <ul class="tags">';
        foreach($obj->getCurrentScenario()->getTags() as $tag) {
            $print .= '
                    <li>@'.$tag.'</li>';
        }
        $print .= '
                </ul>';

        $print .= '
                <h3>
                    <span class="keyword">'.$obj->getCurrentScenario()->getId().' Scenario Outline: </span>
                    <span class="title">'.$obj->getCurrentScenario()->getName().'</span>
                </h3>
                <ol>';

        //TODO path is missing

        return $print;
    }

    /**
     * Renders after an outline.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterOutline($obj)
    {
        return $this->renderAfterScenario($obj);
    }

    /**
     * Renders before a step.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeStep($obj)
    {

        return '';
    }

    /**
     * Renders TableNode arguments.
     *
     * @param TableNode $table
     * @return string  : HTML generated
     */
    public function renderTableNode(TableNode $table){
        $arguments = '<table class="argument"> <thead>';
        $header = $table->getRow(0);
        $arguments .= $this->preintTableRows($header);

        $arguments .= '</thead><tbody>';
        foreach ($table->getHash() as $row) {
            $arguments .= $this->preintTableRows($row);
        }

        $arguments .= '</tbody></table>';
        return $arguments;
    }

    /**
     * Renders table rows.
     *
     * @param array $row
     * @return string  : HTML generated
     */
    public function preintTableRows($row){
        $return = '<tr class="row">';
        foreach ($row as $column) {
            $return .= '<td>' . htmlentities($column) . '</td>';
        }
        $return .= '</tr>';
        return $return;
    }

    /**
     * Renders after a step.
     * @param object : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterStep($obj)
    {
        $feature = $obj->getCurrentFeature();
        $scenario = $obj->getCurrentScenario();

        $steps = $scenario->getSteps();
        $step = end($steps); //needed because of strict standards

        //path displayed only if available (it's not available in undefined steps)
        $strPath = '';
        if($step->getDefinition() !== null) {
            $strPath = $step->getDefinition()->getPath();
        }

        $stepResultClass = '';
        if($step->isPassed()) {
            $stepResultClass = 'passed';
        }
        if($step->isFailed()) {
            $stepResultClass = 'failed';
        }
        if($step->isSkipped()) {
            $stepResultClass = 'skipped';
        }
        if($step->isPending()) {
            $stepResultClass = 'pending';
        }

        $arguments ='';
        $argumentType = $step->getArgumentType();

        if($argumentType == "PyString"){
            $arguments = '<br><pre class="argument">' . htmlentities($step->getArguments()) . '</pre>';
        }

        if ($argumentType == 'Table'){
            $arguments =  '<br><pre class="argument">' . $this->renderTableNode($step->getArguments()) . '</pre>';
        }

        $print = '
                    <li class="'.$stepResultClass.'">
                        <div class="step">
                            <span class="keyword">'.$step->getKeyWord().' </span>
                            <span class="text">'.htmlentities($step->getText()).' </span>
                            <span class="path">'.$strPath.'</span>'
          . $arguments . '
                        </div>';
        $exception = $step->getException();
        if(!empty($exception)) {
            $relativeScreenshotPath = 'assets/screenshots/' . $feature->getScreenshotFolder() . '/' . $scenario->getScreenshotName();
            $fullScreenshotPath = $obj->getOutputPath() . '/' . $relativeScreenshotPath;
            $print .= '
                        <pre class="backtrace">'.$step->getException().'</pre>';
            if(file_exists($fullScreenshotPath))
            {
                $print .= '<a href="' . $relativeScreenshotPath . '">Screenshot</a>';
            }
        }
        $print .= '
                    </li>';

        return $print;
    }

    /**
     * To include CSS
     * @return string  : HTML generated
     */
    public function getCSS() {}

    /**
     * To include JS
     * @return string  : HTML generated
     */
    public function getJS() {}
}
