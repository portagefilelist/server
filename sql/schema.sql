--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2
-- Dumped by pg_dump version 12.2

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: arch; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.arch (
    archid bigint NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: arch_archid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.arch ALTER COLUMN archid ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.arch_archid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: dir; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dir (
    dirid bigint NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: dir_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.dir ALTER COLUMN dirid ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.dir_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: file; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.file (
    fk_pkgid bigint NOT NULL,
    name character varying(2000) NOT NULL,
    path character varying(2000) NOT NULL,
    file character varying(2000) NOT NULL,
    misc character varying(255) NOT NULL,
    fileid bigint NOT NULL
);


--
-- Name: file2arch; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.file2arch (
    fk_fileid bigint NOT NULL,
    fk_archid bigint NOT NULL
);


--
-- Name: file2useflag; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.file2useflag (
    fk_fileid bigint NOT NULL,
    fk_useflagid bigint NOT NULL
);


--
-- Name: file_fileid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.file ALTER COLUMN fileid ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.file_fileid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: pkg; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pkg (
    fk_dirid bigint NOT NULL,
    pkgid bigint NOT NULL,
    name character varying(255) NOT NULL,
    version character varying(255) NOT NULL,
    compiled character varying(255)
);


--
-- Name: pkg_pkgid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.pkg ALTER COLUMN pkgid ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.pkg_pkgid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: useflag; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.useflag (
    useflagid bigint NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: useflag_useflagid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.useflag ALTER COLUMN useflagid ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.useflag_useflagid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: arch arch_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.arch
    ADD CONSTRAINT arch_pkey PRIMARY KEY (name);


--
-- Name: dir dir_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dir
    ADD CONSTRAINT dir_pkey PRIMARY KEY (name);


--
-- Name: file2arch file2arch_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.file2arch
    ADD CONSTRAINT file2arch_pkey PRIMARY KEY (fk_fileid, fk_archid);


--
-- Name: file2useflag file2useflag_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.file2useflag
    ADD CONSTRAINT file2useflag_pkey PRIMARY KEY (fk_fileid, fk_useflagid);


--
-- Name: file file_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.file
    ADD CONSTRAINT file_pkey PRIMARY KEY (fk_pkgid, name, path);


--
-- Name: pkg pkg_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pkg
    ADD CONSTRAINT pkg_pkey PRIMARY KEY (fk_dirid, name, version);


--
-- Name: useflag useflag_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.useflag
    ADD CONSTRAINT useflag_pkey PRIMARY KEY (name);


--
-- PostgreSQL database dump complete
--

