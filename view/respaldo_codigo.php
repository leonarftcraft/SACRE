<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SACREJ</title>
    <link rel="stylesheet" href="view/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/css/style.css">
    <style>
        /* Estilo para los submenús */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -5px;
        }

        /* Mostrar el submenú al pasar el cursor sobre "Registrar" */
        .dropdown-submenu:hover .dropdown-menu {
            display: block;
        }

        .form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 350px;
            background-color: #e7e6e6;
            padding: 20px;
            border-radius: 20px;
            position: relative;
            margin-left: 30%;
            margin-right: 30%;
            margin-top: 5%;
        }

        .title {
            font-size: 28px;
            color: royalblue;
            font-weight: 600;
            letter-spacing: -1px;
            position: relative;
            display: flex;
            align-items: center;
            padding-left: 30px;
        }

        .title::before,
        .title::after {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            border-radius: 50%;
            left: 0px;
            background-color: royalblue;
        }

        .title::before {
            width: 18px;
            height: 18px;
            background-color: royalblue;
        }

        .title::after {
            width: 18px;
            height: 18px;
            animation: pulse 1s linear infinite;
        }

        .message,
        .signin {
            color: rgba(88, 87, 87, 0.822);
            font-size: 14px;
        }

        .signin {
            text-align: center;
        }

        .signin a {
            color: royalblue;
        }

        .signin a:hover {
            text-decoration: underline royalblue;
        }

        .flex {
            display: flex;
            width: 100%;
            gap: 6px;
        }

        .form label {
            position: relative;
        }

        .form label .input {
            width: 100%;
            padding: 10px 10px 20px 10px;
            outline: 0;
            border: 1px solid rgba(105, 105, 105, 0.397);
            border-radius: 10px;
        }

        .form label .input+span {
            position: absolute;
            left: 10px;
            top: 15px;
            color: grey;
            font-size: 0.9em;
            cursor: text;
            transition: 0.3s ease;
        }

        .form label .input:placeholder-shown+span {
            top: 15px;
            font-size: 0.9em;
        }

        .form label .input:focus+span,
        .form label .input:valid+span {
            top: 30px;
            font-size: 0.7em;
            font-weight: 600;
        }

        .form label .input:valid+span {
            color: green;
        }

        .submit {
            border: none;
            outline: none;
            background-color: royalblue;
            padding: 10px;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            transform: .3s ease;
        }

        .submit:hover {
            background-color: rgb(56, 90, 194);
        }

        @keyframes pulse {
            from {
                transform: scale(0.9);
                opacity: 1;
            }

            to {
                transform: scale(1.8);
                opacity: 0;
            }
        }
    </style>



</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="?controller=sacrej&action=cerrarsesion">  <img src="view/images/logo.png" alt="Logo MVC" style="height: 70px; width: auto; margin-right: 20px;">SACREJ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!--Navegador administrador-->
            <div class="collapse navbar-collapse" style="display: none !important;" id="NavAdmin">
                <ul class="navbar-nav ms-auto">

                    <!-- Menú Consultas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownConsultas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Consultas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownConsultas">
                            <li><a class="dropdown-item" href="">Opciones</a></li>
                            <li><a class="dropdown-item" href="">Consultas</a></li>
                        </ul>
                    </li>
                    <!-- Menú Clase -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownRegistro" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Informacion
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownRegistro">
                            
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Otros</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="">Informacion</a></li>
                                    <li><a class="dropdown-item" href="">Videos</a></li>
                                </ul>
                                
                            </li>
                            <!-- Menú desplegable con submenú -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Mas</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="">Informacion</a></li>
                                </ul>
                            </li>
                            <li><a class="dropdown-item" href="">Listas</a></li>
                        </ul>

                    </li>



                    <!-- Menú Usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUsuario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F3F3F3">
                                <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z" />
                            </svg>
                        </a>

                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownUsuario">
                            <li><a class="dropdown-item" href="">Perfil</a></li>
                            <li><a class="dropdown-item" href="?controller=sacrej&action=cerrarsesion">Cerrar Sesión</a></li>
                        </ul>
                    </li>

                </ul>
            </div>

            <!--Navegador Usuario-->
            <div class="collapse navbar-collapse" style="display: none !important;" id="NavUsu">
                <ul class="navbar-nav ms-auto">

                    <!-- Menú Consultas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownConsultas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Consultas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownConsultas">
                            <li><a class="dropdown-item" href="">Opciones</a></li>
                            <li><a class="dropdown-item" href="">Consultas</a></li>
                        </ul>
                    </li>
                    <!-- Menú Clase -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownRegistro" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Informacion
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownRegistro">
                            
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Otros</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="">Informacion</a></li>
                                    <li><a class="dropdown-item" href="">Videos</a></li>
                                </ul>
                                
                            </li>
                            <!-- Menú desplegable con submenú -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Mas</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="">Informacion</a></li>
                                </ul>
                            </li>
                            <li><a class="dropdown-item" href="">Listas</a></li>
                        </ul>

                    </li>



                    <!-- Menú Usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUsuario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F3F3F3">
                                <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z" />
                            </svg>
                        </a>

                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownUsuario">
                            <li><a class="dropdown-item" href="">Perfil</a></li>
                            <li><a class="dropdown-item" href="?controller=sacrej&action=cerrarsesion">Cerrar Sesión</a></li>
                        </ul>
                    </li>


                </ul>
            </div>


          
        </div>
    </nav>