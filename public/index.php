<?php
// On démarre la session, pour la gestion des messages de succès.
session_start();
// Include des fichier nécessaires de connexion à la BDD et des fonctions.
include('../connect.php');
include('../src/functions.php');

// Connexion à la base de donnée avec les constantes définies dans le fichier connect.php
$db = new \PDO(DSN, USER, PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// On récupère la liste des contacts
$req = $db->query("SELECT contact.lastname, contact.firstname, civility.civility
                            FROM contact
                            INNER JOIN civility on contact.civility_id=civility.id
                            ORDER BY contact.lastname");
$contacts = $req->fetchAll(\PDO::FETCH_ASSOC);
$req->closeCursor();

// On récupère la liste de la table civilité pour l'afficher dans le select du formulaire
$req = $db->query("SELECT * FROM civility");
$civilities = $req->fetchAll(\PDO::FETCH_ASSOC);
$req->closeCursor();

// Dans le cas où un formulaire est envoyé
if (isset($_POST) and !empty($_POST)) {
    $formError = [];

    // Traitement des erreurs
    if (empty($_POST['firstname'])) {
        $formError['firstname'] = "Le prénom ne peut pas être vide.";
    }
    if (empty($_POST['lastname'])) {
        $formError['lastname'] = "Le nom ne peut pas être vide.";
    }

    // On créer un tableau avec seulement les id de la table civility
    $civilitiesIds = array_column($civilities, 'id');
    // On vérifie si la valeur du formulaire est bien dans la table des id possible sinon erreur
    if (!in_array($_POST['civility_id'], $civilitiesIds)) {
        $formError['civility'] = "Non valide";
    }

    // Si pas d'erreur, on ajoute dans la base de donnée.
    if (!count($formError)) {
        $req = $db->prepare("INSERT INTO contact (lastname, firstname, civility_id)
                                        VALUES (:lastname, :firstname, :civility_id)");
        $req->bindParam(":lastname", $_POST['lastname'], \PDO::PARAM_STR);
        $req->bindParam(":firstname", $_POST['firstname'], \PDO::PARAM_STR);
        $req->bindParam(":civility_id", $_POST['civility_id'], \PDO::PARAM_INT);

        // ->execute() retourne true ou false, donc on peut vérifier si l'ajout s'est bien passé ou non.
        if ($req->execute()) {
            $req->closeCursor();
            $_SESSION['alert'] = [
                "type" => "success",
                "message" =>
                    "<b>" . fullName($_POST['lastname'], $_POST['firstname']) . "</b> à été ajouté avec succès."
            ];
            header("location: /");
            exit();
        } else {
            $req->closeCursor();
            $_SESSION['alert'] = [
                "type" => "danger",
                "message" => "Une erreur est survenue pendant l'enregistrement, veuillez réessayer."
            ];
        };
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>WCS.S5 - Checkpoint 1</title>
</head>
<body>

<div class="container">
    <div class="row mt-3">
        <div class="col">
            <h1 class="mb-5">Mes contacts</h1>

            <?php if (isset($_SESSION['alert'])) : ?>
                <div class="alert alert-<?= $_SESSION['alert']['type'] ?>">
                    <p class="mb-0"><?= $_SESSION['alert']['message'] ?></p>
                </div>
            <?php endif;
            session_destroy(); ?>

            <h2>Liste des contacts</h2>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Civilité</th>
                    <th scope="col">NOM Prénom</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($contacts as $contact) : ?>
                    <tr>
                        <th scope="row"><?= $contact['civility'] ?></th>
                        <td><?= fullName($contact['lastname'], $contact['firstname']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h2>Ajouter un contact</h2>
            <form action="/" method="POST">
                <div class="row">
                    <div class="col">
                        <label for="form_civility" class="form-label">Civilité</label>
                        <select class="form-control" id="form_civility" name="civility_id">
                            <?php foreach ($civilities as $civility) : ?>
                                <option value="<?= $civility['id'] ?>" <?= isset($_POST['civility_id']) ?
                                    ($_POST['civility_id'] == $civility['id'] ? "selected" : "") : "" ?>>
                                    <?= $civility['civility'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-danger"><?= $formError['civility'] ?? "" ?></small>
                    </div>
                    <div class="col">
                        <label for="form_lastname" class="form-label">Nom</label>
                        <input type="text" class="form-control" placeholder="Nom" name="lastname" id="form_lastname"
                               value="<?= $_POST['lastname'] ?? "" ?>">
                        <small class="text-danger"><?= $formError['lastname'] ?? "" ?></small>
                    </div>
                    <div class="col">
                        <label for="form_firstname" class="form-label">Prénom</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Prénom"
                               name="firstname"
                               id="form_firstname"
                               value="<?= $_POST['firstname'] ?? "" ?>"
                        >
                        <small class="text-danger"><?= $formError['firstname'] ?? "" ?></small>
                    </div>
                    <div class="col-12 mt-3">
                        <button class="btn btn-light float-right">Ajouter</button>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <p class="text-danger"><?= $formError['SQLERROR'] ?? "" ?></p>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
</body>
</html>