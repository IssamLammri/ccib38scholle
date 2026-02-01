// assets/vue/main.js
import { createApp } from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import Hello from './components/Hello.vue';
import NewPage from './pages/NewPage.vue';
import ListPayments from './pages/payment/ListPayments.vue';
import ListInvoice from './pages/invoice/ListInvoice.vue';
import ListRefund from './pages/refund/ListRefund.vue';
import NewRefund from './pages/refund/NewRefund.vue';
import ShowRefund from './pages/refund/ShowRefund.vue';
import Dashboard from './pages/dashboard/Dashboard.vue';
import DashboardParent from './pages/dashboard/DashboardParent.vue';
import RegistrationAcademicSupportList from './pages/registrationPageAcademicSupport/RegistrationAcademicSupportList.vue';
import RegistrationList from './pages/registers/RegistrationList.vue';
import NewSession from './pages/session/NewSession.vue';
import ListInvoiceNew from './pages/session/ListInvoiceNew.vue';
import ShowInvoice from './pages/invoice/ShowInvoice.vue';
import ShowParent from './pages/parent/ShowParent.vue';
import ListParents from './pages/parent/ListParents.vue';
import TeacherShow from './pages/teacher/TeacherShow.vue';
import TeachersList from './pages/teacher/TeachersList.vue';
import TeacherForm from './pages/teacher/TeacherForm.vue';
import StudyClass from './pages/studyClass/StudyClass.vue';
import StudyList from './pages/studyClass/StudyList.vue';
import StudyClassList from './pages/studyClass/StudyClassList.vue';
import EditStudyClass from './pages/studyClass/EditStudyClass.vue';
import NewStudyClass from './pages/studyClass/NewStudyClass.vue';
import PlanningStudyClass from './pages/studyClass/PlanningStudyClass.vue';
import SessionShow from './pages/session/SessionShow.vue';
import SessionEdit from './pages/session/SessionEdit.vue';
import SessionList from './pages/session/SessionList.vue';
import SessionOfDay from './pages/session/SessionOfDay.vue';
import PresencesSessions from './pages/session/PresencesSessions.vue';
import BookEdit from './pages/book/BookEdit.vue';
import MenuList from './pages/MenuList.vue';
import BookList from './pages/book/BookList.vue';
import BookNew from './pages/book/BookNew.vue';
import RegistrationPageAcademicSupport from './pages/RegistrationPageAcademicSupport.vue';
import RegistrationPageArabicCourse from './pages/RegistrationPageArabicCourse.vue';
import RegistrationPageArabicCourseShow from './pages/RegistrationPageArabicCourseShow.vue';
import FooterComponent from "./components/FooterComponent.vue";
import * as bootstrap from 'bootstrap'

// Importer le router.min.js et les données de routing
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import routes from '../../public/js/fos_js_routes.json';

// Initialiser le routing avec les routes générées
Routing.setRoutingData(routes);

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp({});

    // Utilisation globale d'Axios
    app.use(VueAxios, axios);
    app.config.globalProperties.$axios = axios;
    app.config.globalProperties.$bootstrap = bootstrap;

    // Utilisation globale du routing via un plugin
    app.config.globalProperties.$routing = Routing;

    // Enregistrement des composants
    app.component('Hello', Hello);
    app.component('NewPage', NewPage);
    app.component('ListParents', ListParents);
    app.component('ListPayments', ListPayments);
    app.component('ListInvoice', ListInvoice);
    app.component('ListRefund', ListRefund);
    app.component('NewRefund', NewRefund);
    app.component('ShowRefund', ShowRefund);
    app.component('NewSession', NewSession);
    app.component('ListInvoiceNew', ListInvoiceNew);
    app.component('MenuList', MenuList);
    app.component('PresencesSessions', PresencesSessions);
    app.component('TeachersList', TeachersList);
    app.component('TeacherForm', TeacherForm);
    app.component('DashboardParent', DashboardParent);
    app.component('Dashboard', Dashboard);
    app.component('RegistrationList', RegistrationList);
    app.component('TeacherShow', TeacherShow);
    app.component('RegistrationAcademicSupportList', RegistrationAcademicSupportList);
    app.component('ShowInvoice', ShowInvoice);
    app.component('StudyClass', StudyClass);
    app.component('StudyList', StudyList);
    app.component('StudyClassList', StudyClassList);
    app.component('EditStudyClass', EditStudyClass);
    app.component('NewStudyClass', NewStudyClass);
    app.component('ShowParent', ShowParent);
    app.component('PlanningStudyClass', PlanningStudyClass);
    app.component('SessionShow', SessionShow);
    app.component('SessionEdit', SessionEdit);
    app.component('SessionList', SessionList);
    app.component('SessionOfDay', SessionOfDay);
    app.component('RegistrationPageAcademicSupport', RegistrationPageAcademicSupport);
    app.component('RegistrationPageArabicCourse', RegistrationPageArabicCourse);
    app.component('RegistrationPageArabicCourseShow', RegistrationPageArabicCourseShow);
    app.component('FooterComponent', FooterComponent);
    app.component('BookNew', BookNew);
    app.component('BookList', BookList);
    app.component('BookEdit', BookEdit);

    if (document.querySelector('#app')) {
        app.mount('#app');
    } else {
        console.error('Element with id #app not found in DOM.');
    }
});