SET NAMES utf8mb4;

CREATE TABLE categories (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор категории',
	name VARCHAR(255) NOT NULL COMMENT 'Название категории',
	description TEXT NOT NULL COMMENT 'Описание категории',
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания категории в UTC',
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата и время последнего изменения категории в UTC',
	PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Категории статей блога';

CREATE TABLE articles (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор статьи',
	image VARCHAR(255) NOT NULL COMMENT 'Публичный путь к изображению статьи',
	title VARCHAR(255) NOT NULL COMMENT 'Название статьи',
	description TEXT NOT NULL COMMENT 'Краткое описание статьи',
	content TEXT NOT NULL COMMENT 'Полный текст статьи',
	published_at TIMESTAMP NOT NULL COMMENT 'Дата и время публикации статьи в UTC',
	views INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Количество просмотров статьи',
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания статьи в UTC',
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата и время последнего изменения статьи в UTC',
	PRIMARY KEY (id),
	INDEX idx_articles_published_at (published_at, id),
	INDEX idx_articles_views (views, id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Статьи блога';

CREATE TABLE article_categories (
	article_id INT UNSIGNED NOT NULL COMMENT 'Идентификатор статьи',
	category_id INT UNSIGNED NOT NULL COMMENT 'Идентификатор категории статьи',
	PRIMARY KEY (article_id, category_id),
	INDEX idx_article_categories_category (category_id, article_id),
	CONSTRAINT fk_article_categories_article
		FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
	CONSTRAINT fk_article_categories_category
		FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Связи статей с категориями';
