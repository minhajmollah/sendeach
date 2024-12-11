@extends('layouts.doc-page')
@section('title', "WhatsappTools")
@section('sidebar')
<ul class="section-items list-unstyled nav flex-column pb-3">
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#intro"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Introduction</a>
    </li>
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#grab_group"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Grab Group Members</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#Grab_group_members">Procedure</a></li>
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#auto_whatsapp"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>WhatsApp Auto Responder</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#whatsapp_auto_responder">Procedure</a></li>
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#contact_tool"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Contact List Grabber</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#contact_list_grabber">Procedure</a></li>
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#map_data"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Google Map Data Extractor</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#google_map_data_extractor">Procedure</a></li>
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#auto_group"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Auto Group Joiner</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#auto_group_joiner">Procedure</a></li>
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#number_filter"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>WhatsApp Number Filter</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#whatsapp_number_filter">Procedure</a></li>

    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#group_members"><span
        class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Grab Active Group Members</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#grab_group_members">Procedure</a></li>
</ul>
@endsection
@section('content')
<article class="docs-article" id="section-1">
    <header class="docs-header">
        <h1 class="docs-heading">SendEach WhatsApp Tools<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>SendEach provides a range of WhatsApp tools that enable clients to easily gather contacts, chat lists, active members, and other useful data.
                These tools also allow for bulk adding of group members, chat list grabbing, group finding, and even extraction of data from Google Maps.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <!--//section-->
</article>
<article class="docs-article" id="intro">
    <header class="docs-header" >
        <h1 class="docs-heading">Introduction<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>Before you start using the SendEach Whatsapp tools, ensure that you have completed the following steps:</p>
            <h2 class="section-heading">1. Make sure you have successfully initiated your Whatsapp account.</h2>
            <div class="mb-5">

                <img src="/assets/docs/images/doc-pages/whatsapp-tools/whatsapp_tools.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <h2 class="section-heading">2. Choose the specific tool you want to utilize from the SendEach Whatsapp tool section.</h2>
            <div class="mb-5">

                <img src="/assets/docs/images/doc-pages/whatsapp-tools/whatsapp_tool_section.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
        </section>
    </header>
</article>

<article class="docs-article" id="grab_group">
    <header class="docs-header">
        <h1 class="docs-heading" >Grab Group Members<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>With the help of this tool from SendEach, you can easily download the contact details of all the members in a group on WhatsApp. This can be particularly useful for businesses and organizations looking to expand their reach and connect with potential customers or partners.
                Additionally, this tool can help in managing and organizing the contact information of group members in a more efficient manner.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="Grab_group_members">
        <h2 class="section-heading">1. Select Grab Group Contact for fetching the group contact details.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_group_contact.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">2. Click on Initiate to connect your WhatsApp.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/Initiate.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">3. Click on Grab Group Contacts.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_group.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">4. Select the group for which you would like to retrieve contact details.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_contacts.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <div class="alert alert-info mt-2">
            <p>Once you have selected the desired group, simply click on "Select",
                and the tool will automatically download the contact details of all group members in an Excel format for your convenience.</p>
        </div>
    </section>
</article>
<article class="docs-article" id="auto_whatsapp">
    <header class="docs-header">
        <h1 class="docs-heading" >WhatsApp Auto Responder<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>The SendEach Whatsapp auto responder bot is a useful tool for businesses or individuals who receive a large number of messages on WhatsApp. It enables them to create automated responses to save time and improve efficiency.
                With the SendEach Whatsapp auto responder bot, clients can customize their auto responses by adding their own text.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="whatsapp_auto_responder">
        <h2 class="section-heading">1. Select WhatsApp Auto Responder from the tool section.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/whatsapp-tools-sec.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">2. Navigate to Add Rule section to Add a Rule .</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/add-icon.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">3. Add the Rule in User Send Input section then click on Add Message to add the Response.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/attach-msg.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">4. Add the Response Message.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/add-response.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">5. Click on Save and then Click on Start to Set the WhatsApp Auto Responder ON.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/start-bot.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <div class="alert alert-info mt-2">
            <p>"Once you click on 'Start', the SendEach WhatsApp auto responder will begin sending the responses you have inputted, based on the messages sent by the users."</p>
        </div>
    </section>
</article>
<article class="docs-article" id="contact_tool">
    <header class="docs-header">
        <h1 class="docs-heading" >Contact List Grabber<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>The SendEach Contact List Grabber tool is an incredibly useful resource that allows users to extract WhatsApp contacts, including group contacts,
                and conveniently export them into Excel format for effortless utilization.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="contact_list_grabber">
        <h2 class="section-heading">1. Navigate to the Contact list Grabber in WhatsApp Tool Section</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/contact-tool.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">2. Click On Grab all Saved Contacts to get the Saved Contact List.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/saved-contact.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">3. Click On Grab all Group Contacts to get the Contact List of the Groups.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grooup-contact.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <div class="alert alert-info mt-2">
            <p>"Once you click on 'Saved Contacts / Group Contacts', the SendEach Contact list Grabber will
                automatically download the contact details of all group members in an Excel format for your convenience."</p>
        </div>
    </section>
</article>
<article class="docs-article" id="map_data">
    <header class="docs-header">
        <h1 class="docs-heading" >Google Map Data Extractor<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>The "SendEach Google Map Data Extractor" tool is an efficient solution for effortlessly extracting valuable information from Google Maps.
                With just a simple input of text, this tool empowers you to extract essential data, including company names, contact numbers, and email addresses. Moreover,
                you can conveniently download the extracted data in an Excel format, streamlining your workflow and enhancing convenience.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="google_map_data_extractor">
        <h2 class="section-heading">1. Navigate to the Google Map Data Extractor in WhatsApp Tool Section</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_map.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">2. Click on start button then enter the text to be searched and then click on search button.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_map_Start.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">3. Click on Grab Email and then click on Start Grabbing to grab the information.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_map_tick.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">4. Click on export to download the Map data in excel format.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_map_export%20.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <div class="alert alert-info mt-2">
            <p>Once you initiate the export process, the SendEach Google Data Extractor tool will swiftly compile the data you entered into a comprehensive Excel format,
                providing you with the utmost convenience and efficiency in managing and accessing the extracted information.</p>
        </div>
    </section>
</article>
<article class="docs-article" id="auto_group">
    <header class="docs-header">
        <h1 class="docs-heading" >Auto Group Joiner<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>The SendEach Auto group joiner tool is designed to streamline your group management process by effortlessly verifying the join link validity and providing comprehensive insights into the group's joiners.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="auto_group_joiner">
        <h2 class="section-heading">1. Navigate to the Auto Group Joiner in WhatsApp Tool Section</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/auto_group_joiner.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">2. Click on Upload Excel to add the file and click start to manage the group.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/auto_group.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <div class="alert alert-info mt-2">
            <p>Once you initiate the "Start" command, the SendEach Auto group joiner empowers you to efficiently oversee and administer your group,
                while also granting you the ability to effortlessly monitor and keep track of newly joined members in real-time.</p>
        </div>
    </section>
</article>
<article class="docs-article" id="number_filter">
    <header class="docs-header">
        <h1 class="docs-heading" >WhatsApp Number Filter<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>The SendEach WhatsApp number filter provides you with a powerful solution to efficiently filter and organize WhatsApp numbers,
                enabling you to streamline your communication process and effectively manage your contacts with ease.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="whatsapp_number_filter">
        <h2 class="section-heading">1. Navigate to the WhatsApp Number Filter in WhatsApp Tool Section</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/number_filter.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">2. Click on Upload Excel to add the file and set the time interval , then click start to Filter the Number.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/number_filter_start.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <div class="alert alert-info mt-2">
            <p>Once you initiate the "Start" command, the SendEach WhatsApp number filter unleashes its capabilities to assist you in seamlessly filtering and refining your contact list,
                empowering you to efficiently manage and categorize numbers with precision and ease.</p>
        </div>
    </section>
</article>
<article class="docs-article" id="group_members">
    <header class="docs-header">
        <h1 class="docs-heading" >Grab Active Group Members<span class="docs-time">Last updated: 2023-05-07</span>
        </h1>
        <section class="docs-intro">
            <p>The SendEach Grab group active members tool is a powerful solution that enables you to effortlessly identify
                and track the active members within your group. With the added convenience of downloading the results in Excel format,
                you can efficiently analyze and utilize the data for your convenience, making group management a seamless process.</p>
        </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="grab_group_members">
        <h2 class="section-heading">1. Navigate to the Grab Active Group Members in WhatsApp Tool Section</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/garb_active_members.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <h2 class="section-heading">2. Click on Initiate , Select the group then click on start to check the active members, click on export to download the list.</h2>
        <div class="mb-5">

            <img src="/assets/docs/images/doc-pages/whatsapp-tools/grab_active_members_Start.png" alt="login"
                 class="img-fluid mt-2 border">
        </div>
        <div class="alert alert-info mt-2">
            <p>By simply clicking on the "Start" button, the SendEach Grab group active member tool initiates its process, swiftly fetching and providing you with comprehensive
                details of the active members within your group. Subsequently, with a single click on the
                "Export" option, the tool conveniently generates and downloads the list in Excel format, ensuring effortless usability and ease of access for your convenience.</p>
        </div>
    </section>
</article>
@endsection
