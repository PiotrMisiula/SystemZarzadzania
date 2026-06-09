<?php
$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        p.id,
        p.name,
        p.description,
        p.visibility,
        p.access_key,
        p.share_role,
        p.created_at,
        p.owner_id,
        pm.role,
        COUNT(t.id) AS task_count
    FROM projects p
    LEFT JOIN tasks t ON t.project_id = p.id
    LEFT JOIN project_members pm 
        ON pm.project_id = p.id AND pm.user_id = ?
    WHERE p.owner_id = ? OR pm.user_id = ?
    GROUP BY p.id, pm.role
    ORDER BY p.created_at DESC
");

$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

function rolePL($role)
{
    return [
        'admin' => 'Administrator',
        'member' => 'Może edytować',
        'viewer' => 'Tylko odczyt'
    ][$role] ?? $role;
}
?>

<div class="tasks-page">

    <div class="tasks-header">
        <h2>Moje projekty</h2>
        <div class="tasks-count">
            <?= $result->num_rows ?> projektów
        </div>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_key'): ?>
        <div style="color: red; margin-bottom: 15px; font-weight: bold;">Podany klucz dostępu jest nieprawidłowy lub projekt nie istnieje.</div>
    <?php endif; ?>

    <div style="display: flex; gap: 10px; margin-bottom: 25px;">
        <button class="btn btn-primary" onclick="openProjectModal()">
            Nowy projekt
        </button>
        <button class="btn btn-secondary" onclick="openJoinModal()">
            Dołącz do projektu
        </button>
    </div>

    <div id="projectModal" class="modal-overlay" style="display:none;">
        <div class="modal">
            <h3>Nowy projekt</h3>

            <form action="create_project.php" method="POST">

                <label>Nazwa projektu</label>
                <input
                    type="text"
                    name="name"
                    placeholder="Np. Aplikacja mobilna"
                    required>

                <label>Opis</label>
                <textarea
                    name="description"
                    rows="3"
                    placeholder="Opis projektu..."></textarea>

                <label>Widoczność projektu</label>
                <select name="visibility">
                    <option value="private">Prywatny</option>
                    <option value="public">Publiczny</option>
                </select>

                <label>Uprawnienia</label>
                <select name="share_role">
                    <option value="viewer">Tylko odczyt</option>
                    <option value="member">Możliwość edycji</option>
                </select>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProjectModal()">
                        Anuluj
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Zapisz
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div id="joinModal" class="modal-overlay" style="display:none;">
        <div class="modal">
            <h3>Dołącz do projektu</h3>

            <form action="join_project.php" method="POST">
                <label>Klucz dostępu</label>
                <input
                    type="text"
                    name="access_key"
                    placeholder="Np. 123456"
                    required>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeJoinModal()">
                        Anuluj
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Dołącz
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="tasks-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="task-card">

                <div class="task-top">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                </div>

                <p class="desc">
                    <?= nl2br(htmlspecialchars($row['description'])) ?>
                </p>

                <div class="task-meta">
                    <div><b>Utworzono:</b> <?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></div>
                    <div><b>Zadań w projekcie:</b> <?= $row['task_count'] ?></div>
                    <div><b>Dostęp:</b> <?= rolePL($row['share_role']) ?></div>
                </div>

                <div class="badges" style="margin-top:10px;">
                    <span class="badge">
                        <?= $row['visibility'] === 'public' ? 'Publiczny' : 'Prywatny' ?>
                    </span>

                    <span class="badge">
                        <?= rolePL($row['role'] ?? 'admin') ?>
                    </span>
                </div>

                <?php if ($row['visibility'] === 'private' && !empty($row['access_key'])): ?>
                    <div style="margin-top:12px;">
                        <b>Klucz dostępu:</b>
                        <?= htmlspecialchars($row['access_key']) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php" method="GET" style="margin-top:12px;">
                    <input type="hidden" name="page" value="project_view">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button class="btn btn-primary" type="submit" style="width: 100%;">
                        Otwórz projekt
                    </button>
                </form>

                <?php 
                $is_owner = ($row['owner_id'] == $user_id);
                $is_admin = ($row['role'] === 'admin');
                if ($is_owner || $is_admin): 
                ?>
                <div style="display: flex; gap: 5px; margin-top: 5px;">
                    <button class="btn btn-secondary" style="flex: 1;" onclick='openEditProjectModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                        Edytuj
                    </button>
                    <?php if ($is_owner): ?>
                    <form action="delete_project.php" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć ten projekt? Wszystkie zadania zostaną bezpowrotnie usunięte.');" style="margin: 0; flex: 1; display: flex;">
                        <input type="hidden" name="project_id" value="<?= $row['id'] ?>">
                        <button class="btn btn-secondary" type="submit" style="background-color: #dc3545; color: white; width: 100%;">Usuń</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        <?php endwhile; ?>
    </div>

</div>

<script>
    function openProjectModal() {
        document.getElementById("projectModal").style.display = "flex";
    }

    function closeProjectModal() {
        document.getElementById("projectModal").style.display = "none";
    }

    function openJoinModal() {
        document.getElementById("joinModal").style.display = "flex";
    }

    function closeJoinModal() {
        document.getElementById("joinModal").style.display = "none";
    }

    function openEditProjectModal(project) {
        const modal = document.createElement("div");
        modal.className = "modal-overlay";
        modal.innerHTML = `
            <div class="modal">
                <h3>Edytuj projekt</h3>
                <form action="edit_project.php" method="POST">
                    <input type="hidden" name="project_id" value="${project.id}">

                    <label>Nazwa projektu</label>
                    <input type="text" name="name" value="${project.name}" required>

                    <label>Opis</label>
                    <textarea name="description" rows="3">${project.description || ''}</textarea>

                    <label>Widoczność projektu</label>
                    <select name="visibility">
                        <option value="private" ${project.visibility === 'private' ? 'selected' : ''}>Prywatny</option>
                        <option value="public" ${project.visibility === 'public' ? 'selected' : ''}>Publiczny</option>
                    </select>

                    <label>Uprawnienia dołączających</label>
                    <select name="share_role">
                        <option value="viewer" ${project.share_role === 'viewer' ? 'selected' : ''}>Tylko odczyt</option>
                        <option value="member" ${project.share_role === 'member' ? 'selected' : ''}>Możliwość edycji</option>
                    </select>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" id="closeEditModalBtn">Anuluj</button>
                        <button type="submit" class="btn btn-primary">Zapisz</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
        modal.querySelector("#closeEditModalBtn").onclick = () => modal.remove();
    }
</script>

<?php
$stmt->close();
$conn->close();
?>