<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use SoapClient;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return ''; //$this->render('index');
    }

    public function actionSoap()
    {
        $wsdl = 'http://185.121.204.45/MHT/CalmAPI/ContentService.asmx?WSDL';
/*
        $client = new SoapClient($wsdl);
        $result = $client->Search([
            'dbname' => 'Catalog',
            'ElementSet' => 'DC',
            'Expr' => 'test',
        ]);

        $sessionID = ($client->__getCookies())['ASP.NET_SessionId'][0];

        $client->__setCookie('ASP.NET_SessionId', $sessionID);

        $result = $client->Overview([
            'dbname' => 'Catalog',
            'ElementSet' => 'DC',
            'Expr' => 'test',

            "Elements" => 'Title, Date',
            "xfrom"     => 0,
            "n"         => 10
        ]);
*/
        $result = 'rrr';
        return $this->render('soap_search', ['response' => $result]);
    }

    public function actionSearch()
    {
        return $this->render('wf_search-result');
    }

    public function actionDetailpage()
    {
        return $this->render('detail-page');
    }

    public function actionCarouselpage()
    {
        return $this->render('carousel');
    }

    public function actionReviewpage()
    {
        return $this->render('google_review');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
